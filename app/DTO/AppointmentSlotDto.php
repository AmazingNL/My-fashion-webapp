<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class AppointmentSlotDto implements JsonSerializable
{
    public function __construct(
        public readonly int $slotId,
        public readonly string $appointmentDate,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly bool $isAvailable,
        public readonly ?string $createdAt = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}