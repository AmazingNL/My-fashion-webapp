<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class AppointmentRequestDto implements JsonSerializable
{
    public function __construct(
        public readonly int $slotId,
        public readonly ?string $designType,
        public readonly ?string $notes,
        public readonly string $status = ''
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}