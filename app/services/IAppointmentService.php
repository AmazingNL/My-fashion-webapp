<?php

namespace App\Services;

use App\Models\AppointmentStatus;

interface IAppointmentService
{
    // customer
    public function getUserAppointments(int $userId): array;
    public function countPending(): int;
    public function getAvailableSlotsByDate(string $date): array;
    public function book(int $userId, int $slotId, ?string $designType, ?string $notes): int;
    public function updateAppointmentSlot(int $userId, int $appointmentId, int $newSlotId): void;
    public function updateAppointmentDetails(int $userId, int $appointmentId, ?string $designType, ?string $notes): void;
    public function cancel(int $userId, int $appointmentId): void;

    // admin
    public function adminGetAllAppointments(): array;
    public function adminAddSlot(string $date, string $startTime, string $endTime): int;
    public function adminAddMonthlySlots(
        string $startDate,
        string $firstStartTime,
        string $firstEndTime,
        string $secondStartTime,
        string $secondEndTime,
        int $days = 30
    ): int;
    public function adminSetStatus(int $appointmentId, AppointmentStatus $status): void;
}
