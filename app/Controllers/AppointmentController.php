<?php

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Models\AppointmentStatus;
use App\Services\IAppointmentService;

final class AppointmentController extends ControllerBase
{
    public function __construct(private IAppointmentService $service)
    {
    }

    // =========================
    // CUSTOMER ROUTES
    // =========================

    public function index(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = (int) ($this->currentUserId() ?? 0);
        if ($userId <= 0) {
            $this->redirect('/?error=login_required');
            return;
        }

            [$success, $error] = $this->consumeFlash('appointment');
        $appointments = $this->service->getUserAppointments($userId);

        $this->render('Appointment/Index', [
            'title' => 'My Appointments',
            'appointments' => $appointments,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function bookForm(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $selectedDate = trim((string) $this->input('date', ''));
            [$success, $error] = $this->consumeFlash('appointment');

        $slots = [];
        if ($selectedDate !== '') {
            try {
                $slots = $this->service->getAvailableSlotsByDate($selectedDate);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $this->render('Appointment/Book', [
            'title' => 'Book Appointment',
            'selectedDate' => $selectedDate,
            'slots' => $slots,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function book(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int) $this->currentUserId();

        $slotIdRaw = $this->input('slotId');
        $slotId = (int) $slotIdRaw;

        if ($slotId < 1) {
            $this->setFlash('appointment', 'Please select a valid time slot.', 'error');
            $this->redirect('/appointments/book');
            return;
        }

        $designType = trim((string) $this->input('designType', '')) ?: null;
        $notes = trim((string) $this->input('notes', '')) ?: null;

        try {
            $id = $this->service->book($userId, $slotId, $designType, $notes);
            $this->setFlash('appointment', 'Appointment booked successfully.', 'success');
            $this->redirect('/appointments');
        } catch (\Throwable $e) {
            $this->setFlash('appointment', $e->getMessage(), 'error');
            $date = trim((string) $this->input('date', ''));
            $this->redirect('/appointments/book' . ($date !== '' ? '?date=' . urlencode($date) : ''));
        }
    }

    public function editForm(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = (int) ($this->currentUserId() ?? 0);
        if ($userId <= 0) {
            $this->redirect('/?error=login_required');
            return;
        }

        $appointment = $this->findUserAppointment($userId, $id);
        if ($appointment === null) {
            $this->setFlash('appointment', 'Appointment not found.', 'error');
            $this->redirect('/appointments');
            return;
        }

        $selectedDate = trim((string) $this->input('date', ''));
        if ($selectedDate === '') {
            $selectedDate = (string) ($appointment['appointmentDate'] ?? '');
        }

        [$success, $error] = $this->consumeFlash('appointment');
        $slots = [];
        if ($selectedDate !== '') {
            try {
                $slots = $this->service->getAvailableSlotsByDate($selectedDate);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $this->render('Appointment/Edit', [
            'title' => 'Update Appointment',
            'appointmentId' => $id,
            'appointment' => $appointment,
            'selectedDate' => $selectedDate,
            'slots' => $slots,
            'success' => $success,
            'error' => $error,
        ]);
    }


    public function updateSlot(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int) $this->currentUserId();
        $newSlotId = (int) $this->input('slotId');

        try {
            $this->service->updateAppointmentSlot($userId, $id, $newSlotId);
            $this->setFlash('appointment', 'Appointment updated successfully.', 'success');
            $this->redirect('/appointments');
        } catch (\Throwable $e) {
            $this->setFlash('appointment', $e->getMessage(), 'error');
            $date = trim((string) $this->input('date', ''));
            $this->redirect('/appointments/' . $id . '/edit' . ($date !== '' ? '?date=' . urlencode($date) : ''));
        }
    }

    public function updateDetails(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int) $this->currentUserId();
        $designType = trim((string) $this->input('designType', '')) ?: null;
        $notes = trim((string) $this->input('notes', '')) ?: null;

        try {
            $this->service->updateAppointmentDetails($userId, $id, $designType, $notes);
            $this->setFlash('appointment', 'Details saved.', 'success');
            $this->redirect('/appointments');
        } catch (\Throwable $e) {
            $this->setFlash('appointment', $e->getMessage(), 'error');
            $this->redirect('/appointments/' . $id . '/edit');
        }
    }

    public function cancel(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int) $this->currentUserId();

        try {
            $this->service->cancel($userId, $id);
            $this->setFlash('appointment', 'Appointment cancelled.', 'success');
            $this->redirect('/appointments');
        } catch (\Throwable $e) {
            $this->setFlash('appointment', $e->getMessage(), 'error');
            $this->redirect('/appointments');
        }
    }

    // =========================
    // ADMIN ROUTES
    // =========================

    public function adminIndex(): void
    {
        Middleware::requireAdmin();
        $appointments = $this->service->adminGetAllAppointments();
        [$success, $error] = $this->consumeFlash('admin');

        $this->render('Admin/Appointment/Index', [
            'title' => 'Appointments',
            'appointments' => $appointments,
            'success' => $success,
            'error' => $error,
        ], 'admin');
    }

    public function adminAddSlot(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $date = (string) $this->input('appointmentDate');
        $start = (string) $this->input('startTime');
        $end = (string) $this->input('endTime');
        $bulkMonth = (string) $this->input('bulkMonth', '') === '1';
        $secondStart = (string) $this->input('secondStartTime', '');
        $secondEnd = (string) $this->input('secondEndTime', '');

        try {
            if ($bulkMonth) {
                $created = $this->service->adminAddMonthlySlots($date, $start, $end, $secondStart, $secondEnd, 30);
                $this->setFlash('admin', 'Monthly slots created: ' . $created, 'success');
            } else {
                $this->service->adminAddSlot($date, $start, $end);
                $this->setFlash('admin', 'Slot added successfully.', 'success');
            }
            $this->redirect('/admin/appointments');
        } catch (\Throwable $e) {
            $this->setFlash('admin', $e->getMessage(), 'error');
            $this->redirect('/admin/appointments');
        }
    }

    public function adminSetStatus(int $id): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        try {
            $status = AppointmentStatus::from((string) $this->input('status'));
            $this->service->adminSetStatus($id, $status);
        } catch (\ValueError $e) {
            $this->setFlash('admin', 'Invalid appointment status.', 'error');
            $this->redirect('/admin/appointments');
            return;
        } catch (\Throwable $e) {
            $this->setFlash('admin', $e->getMessage(), 'error');
            $this->redirect('/admin/appointments');
            return;
        }

        $this->setFlash('admin', 'Status updated.', 'success');
        $this->redirect('/admin/appointments');
    }

    private function findUserAppointment(int $userId, int $appointmentId): ?array
    {
        $appointments = $this->service->getUserAppointments($userId);
        foreach ($appointments as $appointment) {
            if ((int) ($appointment['appointmentId'] ?? 0) === $appointmentId) {
                return $appointment;
            }
        }

        return null;
    }

}
