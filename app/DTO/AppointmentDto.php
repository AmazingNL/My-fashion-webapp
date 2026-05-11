<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class AppointmentDto implements JsonSerializable
{
    public function __construct(
        public readonly int $appointmentId,
        public readonly int $userId,
        public readonly int $slotId,
        public readonly ?string $designType,
        public readonly ?string $notes,
        public readonly string $status,
        public readonly ?string $appointmentDate = null,
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}