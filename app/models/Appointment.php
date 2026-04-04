<?php

namespace App\Models;

class Appointment
{
    public ?int $appointmentId;
    public int $userId;
    public int $slotId;
    public ?string $designType;
    public ?string $notes;
    public AppointmentStatus $status;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        ?int $appointmentId,
        int $userId,
        int $slotId,
        ?string $designType,
        ?string $notes,
        AppointmentStatus $status,
        ?string $createdAt,
        ?string $updatedAt
    ) {
        $this->appointmentId = $appointmentId;
        $this->userId = $userId;
        $this->slotId = $slotId;
        $this->designType = $designType;
        $this->notes = $notes;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

}
