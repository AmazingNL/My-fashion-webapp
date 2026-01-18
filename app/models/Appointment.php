<?php

namespace App\Models;

class Appointment
{
    public function __construct(
        private ?int $appointmentId,
        private int $userId,
        private int $slotId,
        private ?string $designType,
        private ?string $notes,
        private AppointmentStatus $status,
        private ?string $createdAt,
        private ?string $updatedAt
    ) {}

    public function getAppointmentId(): ?int { return $this->appointmentId; }
    public function getUserId(): int { return $this->userId; }
    public function getSlotId(): int { return $this->slotId; }
    public function getDesignType(): ?string { return $this->designType; }
    public function getNotes(): ?string { return $this->notes; }
    public function getStatus(): AppointmentStatus { return $this->status; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}
