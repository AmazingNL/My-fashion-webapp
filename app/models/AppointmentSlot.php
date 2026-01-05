<?php

namespace App\Models;

final class AppointmentSlot
{
    public function __construct(
        private ?int $slotId,
        private string $appointmentDate,
        private string $startTime,
        private string $endTime,
        private bool $isAvailable,
        private ?string $createdAt
    ) {}

    public function getSlotId(): ?int { return $this->slotId; }
    public function getAppointmentDate(): string { return $this->appointmentDate; }
    public function getStartTime(): string { return $this->startTime; }
    public function getEndTime(): string { return $this->endTime; }
    public function isAvailable(): bool { return $this->isAvailable; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
}
