<?php

namespace App\Mappers;

use App\DTO\AppointmentSlotDto;

class AppointmentSlotMapper
{
    public static function mapToAppointmentSlotDto($slot): AppointmentSlotDto
    {
        return new AppointmentSlotDto(
            (int) self::value($slot, 'slotId', 0),
            (string) self::value($slot, 'appointmentDate', ''),
            (string) self::value($slot, 'startTime', ''),
            (string) self::value($slot, 'endTime', ''),
            (bool) self::value($slot, 'isAvailable', true),
            self::nullableString(self::value($slot, 'createdAt'))
        );
    }

    public static function mapToAppointmentSlotDtos(array $slots): array
    {
        return array_map(static fn($slot): AppointmentSlotDto => self::mapToAppointmentSlotDto($slot), $slots);
    }

    private static function value($source, string $key, $default = null)
    {
        if (is_array($source)) {
            return $source[$key] ?? $default;
        }

        return is_object($source) ? ($source->{$key} ?? $default) : $default;
    }

    private static function nullableString($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}