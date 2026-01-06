<?php

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Models\AppointmentStatus;
use App\Services\IAppointmentService;

final class AppointmentController extends ControllerBase
{
    public function __construct(private IAppointmentService $service) {}

    // =========================
    // CUSTOMER ROUTES
    // =========================

    public function index(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $userId = $this->currentUserId();
        $appointments = $this->service->getUserAppointments((int)$userId);

        $this->render('Appointment/Index', [
            'title' => 'My Appointments',
            'appointments' => $appointments
        ]);
    }

    public function bookForm(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $this->render('Appointment/Book', [
            'title' => 'Book Appointment'
        ]);
    }

    public function book(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int)$this->currentUserId();
        $slotId = (int)$this->input('slotId');
        $designType = trim((string)$this->input('designType', '')) ?: null;
        $notes = trim((string)$this->input('notes', '')) ?: null;

        try {
            $id = $this->service->book($userId, $slotId, $designType, $notes);
            $this->redirect('/appointments?success=booked&id=' . $id);
        } catch (\Throwable $e) {
            $this->redirect('/appointments/book?error=' . urlencode($e->getMessage()));
        }
    }

    public function editForm(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        // View can call API to load available slots by date
        $this->render('Appointments/Edit', [
            'title' => 'Update Appointment',
            'appointmentId' => $id
        ]);
    }

    public function updateSlot(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int)$this->currentUserId();
        $newSlotId = (int)$this->input('slotId');

        try {
            $this->service->updateAppointmentSlot($userId, $id, $newSlotId);
            $this->redirect('/appointments?success=updated');
        } catch (\Throwable $e) {
            $this->redirect('/appointments/' . $id . '/edit?error=' . urlencode($e->getMessage()));
        }
    }

    public function updateDetails(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int)$this->currentUserId();
        $designType = trim((string)$this->input('designType', '')) ?: null;
        $notes = trim((string)$this->input('notes', '')) ?: null;

        try {
            $this->service->updateAppointmentDetails($userId, $id, $designType, $notes);
            $this->redirect('/appointments?success=saved');
        } catch (\Throwable $e) {
            $this->redirect('/appointments/' . $id . '/edit?error=' . urlencode($e->getMessage()));
        }
    }

    public function cancel(int $id): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();
        $this->validateCsrf();

        $userId = (int)$this->currentUserId();

        try {
            $this->service->cancel($userId, $id);
            $this->redirect('/appointments?success=cancelled');
        } catch (\Throwable $e) {
            $this->redirect('/appointments?error=' . urlencode($e->getMessage()));
        }
    }

    // API endpoint for JS date picker
    public function apiAvailableSlots(): void
    {
        Middleware::requireAuth();
        Middleware::requireCustomer();

        $date = (string)$this->input('date', '');
        if (!$date) $this->jsonResponse(['error' => 'date_required'], 400);

        $slots = $this->service->getAvailableSlotsByDate($date);

        $payload = array_map(fn($s) => [
            'slotId' => $s->getSlotId(),
            'appointmentDate' => $s->getAppointmentDate(),
            'startTime' => $s->getStartTime(),
            'endTime' => $s->getEndTime(),
        ], $slots);

        $this->jsonResponse(['slots' => $payload]);
    }

    // =========================
    // ADMIN ROUTES
    // =========================

    public function adminIndex(): void
    {
        Middleware::requireAdmin();
        $appointments = $this->service->adminGetAllAppointments();

        $this->render('Admin/Appointment/Index', [
            'title' => 'Appointments',
            'appointments' => $appointments
        ]);
    }

    public function adminAddSlot(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $date = (string)$this->input('appointmentDate');
        $start = (string)$this->input('startTime');
        $end = (string)$this->input('endTime');

        try {
            $this->service->adminAddSlot($date, $start, $end);
            $this->redirect('/admin/appointments?success=slot_added');
        } catch (\Throwable $e) {
            $this->redirect('/admin/appointments?error=' . urlencode($e->getMessage()));
        }
    }

    public function adminSetStatus(int $id): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $status = AppointmentStatus::fromDb((string)$this->input('status'));
        $this->service->adminSetStatus($id, $status);

        $this->redirect('/admin/appointments?success=status_updated');
    }
}
