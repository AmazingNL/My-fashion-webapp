<?php

namespace App\Models;

enum AppointmentStatus: string
{
    case PENDING   = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';

    public static function fromDb(?string $value): self
    {
        return self::tryFrom(strtoupper((string)$value)) ?? self::PENDING;
    }
}
