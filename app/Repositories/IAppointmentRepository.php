<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Models\AppointmentStatus;

interface IAppointmentRepository
{
    public function getAll(): array;
    public function countByStatus(AppointmentStatus $status): int;
    public function getAllWithSlot(): array;

    public function findById(int $id): ?array;         // appointment + slot

    public function findByUserId(int $userId): array;  // appointment + slot
    public function create(Appointment $appointment): int;

    public function updateSlot(int $appointmentId, int $slotId): bool;
    public function updateDetails(int $appointmentId, ?string $designType, ?string $notes): bool;

    public function setStatus(int $appointmentId, AppointmentStatus $status): bool;
    public function delete(int $appointmentId): bool;

    public function autoCancelPastAppointments(): int; // returns affected rows


    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
