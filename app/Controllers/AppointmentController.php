<?php

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Mappers\AppointmentMapper;
use App\Mappers\AppointmentRequestMapper;
use App\Mappers\AppointmentSlotMapper;
use App\Mappers\AppointmentSlotRequestMapper;
use App\Models\AppointmentStatus;
use App\Services\Interfaces\IAppointmentService;

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
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->jsonResponse($this->error('Authentication is required.'), 401);
            }

            $appointments = $this->service->getUserAppointments($userId);

            $this->jsonResponse($this->success([
                'appointments' => AppointmentMapper::mapToAppointmentDtos($appointments),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load appointments.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function bookForm(): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $selectedDate = trim((string) $this->input('date', ''));

            $slots = [];
            if ($selectedDate !== '') {
                $slots = $this->service->getAvailableSlotsByDate($selectedDate);
            }

            $this->jsonResponse($this->success([
                'selectedDate' => $selectedDate,
                'slots' => AppointmentSlotMapper::mapToAppointmentSlotDtos($slots),
                'error' => '',
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load appointment booking form.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function book(): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) $this->currentUserId();
            $dto = AppointmentRequestMapper::mapToAppointmentRequestDto($this->requestData());
            $slotId = $dto->slotId;

            if ($slotId < 1) {
                $this->jsonResponse($this->error('Please select a valid time slot.'), 422);
            }

            $id = $this->service->book($userId, $slotId, $dto->designType, $dto->notes);
            $this->jsonResponse($this->success([
                'appointmentId' => $id,
            ], 'Appointment booked successfully.'), 201);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function editForm(int $id): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) ($this->currentUserId() ?? 0);
            if ($userId <= 0) {
                $this->jsonResponse($this->error('Authentication is required.'), 401);
            }

            $appointment = $this->findUserAppointment($userId, $id);
            if ($appointment === null) {
                $this->jsonResponse($this->error('Appointment not found.'), 404);
            }

            $selectedDate = trim((string) $this->input('date', ''));
            if ($selectedDate === '') {
                $selectedDate = (string) ($appointment['appointmentDate'] ?? '');
            }

            $slots = [];
            if ($selectedDate !== '') {
                $slots = $this->service->getAvailableSlotsByDate($selectedDate);
            }

            $this->jsonResponse($this->success([
                'appointmentId' => $id,
                'appointment' => AppointmentMapper::mapToAppointmentDto($appointment),
                'selectedDate' => $selectedDate,
                'slots' => AppointmentSlotMapper::mapToAppointmentSlotDtos($slots),
                'error' => '',
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load appointment edit form.', ['detail' => $e->getMessage()]), 500);
        }
    }


    public function updateSlot(int $id): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) $this->currentUserId();
            $dto = AppointmentRequestMapper::mapToAppointmentRequestDto($this->requestData());
            $newSlotId = $dto->slotId;

            $this->service->updateAppointmentSlot($userId, $id, $newSlotId);
            $this->jsonResponse($this->success([
                'appointmentId' => $id,
                'slotId' => $newSlotId,
            ], 'Appointment updated successfully.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function updateDetails(int $id): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) $this->currentUserId();
            $dto = AppointmentRequestMapper::mapToAppointmentRequestDto($this->requestData());

            $this->service->updateAppointmentDetails($userId, $id, $dto->designType, $dto->notes);
            $this->jsonResponse($this->success([
                'appointmentId' => $id,
                'designType' => $dto->designType,
                'notes' => $dto->notes,
            ], 'Details saved.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function cancel(int $id): void
    {
        try {
            Middleware::requireAuth();
            Middleware::requireCustomer();

            $userId = (int) $this->currentUserId();

            $this->service->cancel($userId, $id);
            $this->jsonResponse($this->success([
                'appointmentId' => $id,
            ], 'Appointment cancelled.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    // =========================
    // ADMIN ROUTES
    // =========================

    public function adminIndex(): void
    {
        try {
            Middleware::requireAdmin();
            $appointments = $this->service->adminGetAllAppointments();

            $this->jsonResponse($this->success([
                'appointments' => AppointmentMapper::mapToAppointmentDtos($appointments),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load admin appointments.', ['detail' => $e->getMessage()]), 500);
        }
    }

    public function adminAddSlot(): void
    {
        try {
            Middleware::requireAdmin();

            $dto = AppointmentSlotRequestMapper::mapToAppointmentSlotRequestDto($this->requestData());

            if ($dto->bulkMonth) {
                $created = $this->service->adminAddMonthlySlots($dto->appointmentDate, $dto->startTime, $dto->endTime, $dto->secondStartTime, $dto->secondEndTime, 30);
                $this->jsonResponse($this->success([
                    'created' => $created,
                ], 'Monthly slots created: ' . $created), 201);
            } else {
                $this->service->adminAddSlot($dto->appointmentDate, $dto->startTime, $dto->endTime);
                $this->jsonResponse($this->success([
                    'appointmentDate' => $dto->appointmentDate,
                    'startTime' => $dto->startTime,
                    'endTime' => $dto->endTime,
                ], 'Slot added successfully.'), 201);
            }
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 422);
        }
    }

    public function adminSetStatus(int $id): void
    {
        try {
            Middleware::requireAdmin();

            $dto = AppointmentRequestMapper::mapToAppointmentRequestDto($this->requestData());
            $status = AppointmentStatus::from($dto->status);
            $this->service->adminSetStatus($id, $status);

            $this->jsonResponse($this->success([
                'appointmentId' => $id,
                'status' => $status->value,
            ], 'Status updated.'));
        } catch (\ValueError $e) {
            $this->jsonResponse($this->error('Invalid appointment status.'), 422);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error($e->getMessage()), 500);
        }
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
