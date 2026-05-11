<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class AppointmentSlotRequestDto implements JsonSerializable
{
    public function __construct(
        public readonly string $appointmentDate,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly bool $bulkMonth = false,
        public readonly string $secondStartTime = '',
        public readonly string $secondEndTime = ''
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}