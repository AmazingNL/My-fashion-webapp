<?php

namespace App\Repositories;

use App\Models\AppointmentSlot;

interface IAppointmentSlotRepository
{
    public function getAll(): array;
    public function findById(int $slotId): ?AppointmentSlot;

    public function findByDate(string $date): array;
    public function findAvailableByDate(string $date): array;

    public function create(AppointmentSlot $slot): int;
    public function setAvailability(int $slotId, bool $available): bool;

    public function lockAvailableSlotForUpdate(int $slotId): ?array; // row array



    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
