<?php

namespace App\Models;

class AppointmentSlot
{
    public ?int $slotId;
    public string $appointmentDate;
    public string $startTime;
    public string $endTime;
    public bool $isAvailable;
    public ?string $createdAt;

    public function __construct(
        ?int $slotId,
        string $appointmentDate,
        string $startTime,
        string $endTime,
        bool $isAvailable,
        ?string $createdAt
    ) {
        $this->slotId = $slotId;
        $this->appointmentDate = $appointmentDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->isAvailable = $isAvailable;
        $this->createdAt = $createdAt;
    }


}
