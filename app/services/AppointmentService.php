<?php

namespace App\Services;

use Exception;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\AppointmentStatus;
use App\Repositories\IAppointmentRepository;
use App\Repositories\IAppointmentSlotRepository;

final class AppointmentService implements IAppointmentService
{
    public function __construct(
        private IAppointmentRepository $appointments,
        private IAppointmentSlotRepository $slots
    ) {
    }

    public function getUserAppointments(int $userId): array
    {
        $this->appointments->autoCancelPastAppointments();
        return $this->appointments->findByUserId($userId);
    }

    public function countPending(): int
    {
        return $this->appointments->countByStatus(AppointmentStatus::PENDING);
    }

    public function getAvailableSlotsByDate(string $date): array
    {
        return $this->slots->findAvailableByDate($date);
    }

    public function book(int $userId, int $slotId, ?string $designType, ?string $notes): int
    {
        // transaction: lock slot, mark unavailable, insert appointment
        $this->slots->beginTransaction();
        try {
            $slotRow = $this->slots->lockAvailableSlotForUpdate($slotId);
            if (!$slotRow) {
                throw new Exception("Slot not available anymore.");
            }

            // mark slot unavailable
            $this->slots->setAvailability($slotId, false);

            $appointment = new Appointment(
                null,
                $userId,
                $slotId,
                $designType,
                $notes,
                AppointmentStatus::PENDING,
                null,
                null
            );

            $id = $this->appointments->create($appointment);

            $this->slots->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->slots->rollBack();
            throw new Exception($e->getMessage(), 0, $e);
        }
    }

    public function updateAppointmentSlot(int $userId, int $appointmentId, int $newSlotId): void
    {
        $current = $this->appointments->findById($appointmentId);
        if (!$current || (int) $current['userId'] !== $userId) {
            throw new Exception("Appointment not found.");
        }

        if (in_array($current['status'], ['CANCELLED', 'COMPLETED'], true)) {
            throw new Exception("You can't change a cancelled/completed appointment.");
        }

        $oldSlotId = (int) $current['slotId'];

        $this->slots->beginTransaction();
        try {
            $slotRow = $this->slots->lockAvailableSlotForUpdate($newSlotId);
            if (!$slotRow) {
                throw new Exception("New slot not available.");
            }

            // reserve new, free old
            $this->slots->setAvailability($newSlotId, false);
            $this->slots->setAvailability($oldSlotId, true);

            $this->appointments->updateSlot($appointmentId, $newSlotId);

            // optionally: set back to PENDING after reschedule
            $this->appointments->setStatus($appointmentId, AppointmentStatus::PENDING);

            $this->slots->commit();
        } catch (\Throwable $e) {
            $this->slots->rollBack();
            throw new Exception($e->getMessage(), 0, $e);
        }
    }

    public function updateAppointmentDetails(int $userId, int $appointmentId, ?string $designType, ?string $notes): void
    {
        $current = $this->appointments->findById($appointmentId);
        if (!$current || (int) $current['userId'] !== $userId) {
            throw new Exception("Appointment not found.");
        }
        $this->appointments->updateDetails($appointmentId, $designType, $notes);
    }

    public function cancel(int $userId, int $appointmentId): void
    {
        $current = $this->appointments->findById($appointmentId);
        if (!$current || (int) $current['userId'] !== $userId) {
            throw new Exception("Appointment not found.");
        }

        if (in_array($current['status'], ['CANCELLED', 'COMPLETED'], true)) {
            return;
        }

        $slotId = (int) $current['slotId'];

        $this->slots->beginTransaction();
        try {
            $this->appointments->setStatus($appointmentId, AppointmentStatus::CANCELLED);
            $this->slots->setAvailability($slotId, true);
            $this->slots->commit();
        } catch (\Throwable $e) {
            $this->slots->rollBack();
            throw new Exception($e->getMessage(), 0, $e);
        }
    }

    public function adminGetAllAppointments(): array
    {
        $this->appointments->autoCancelPastAppointments();
        return $this->appointments->getAllWithSlot();
    }

    public function adminAddSlot(string $date, string $startTime, string $endTime): int
    {
        $slot = new AppointmentSlot(null, $date, $startTime, $endTime, true, null);
        return $this->slots->create($slot);
    }

    public function adminAddMonthlySlots(
        string $startDate,
        string $firstStartTime,
        string $firstEndTime,
        string $secondStartTime,
        string $secondEndTime,
        int $days = 30
    ): int {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        if (!$start || $start->format('Y-m-d') !== $startDate) {
            throw new Exception('Invalid start date.');
        }

        if (
            $firstStartTime === '' ||
            $firstEndTime === '' ||
            $secondStartTime === '' ||
            $secondEndTime === ''
        ) {
            throw new Exception('Both daily time slots are required for monthly generation.');
        }

        if ($firstStartTime >= $firstEndTime || $secondStartTime >= $secondEndTime) {
            throw new Exception('Each slot must have an end time later than start time.');
        }

        $ranges = [
            [$firstStartTime, $firstEndTime],
            [$secondStartTime, $secondEndTime],
        ];

        $created = 0;
        for ($i = 0; $i < $days; $i++) {
            $date = $start->modify('+' . $i . ' day')->format('Y-m-d');
            $existing = $this->slots->findByDate($date);
            $existingStarts = [];

            foreach ($existing as $slot) {
                $existingStarts[(string) ($slot->startTime ?? '')] = true;
            }

            foreach ($ranges as [$slotStart, $slotEnd]) {
                if (isset($existingStarts[$slotStart])) {
                    continue;
                }

                $this->slots->create(new AppointmentSlot(null, $date, $slotStart, $slotEnd, true, null));
                $created++;
            }
        }

        return $created;
    }

    public function adminSetStatus(int $appointmentId, AppointmentStatus $status): void
    {
        $this->appointments->setStatus($appointmentId, $status);
    }
}
