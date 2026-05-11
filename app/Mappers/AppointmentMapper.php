<?php

namespace App\Mappers;

use App\DTO\AppointmentDto;

class AppointmentMapper
{
    public static function mapToAppointmentDto($appointment): AppointmentDto
    {
        return new AppointmentDto(
            (int) self::value($appointment, 'appointmentId', 0),
            (int) self::value($appointment, 'userId', 0),
            (int) self::value($appointment, 'slotId', 0),
            self::nullableString(self::value($appointment, 'designType')),
            self::nullableString(self::value($appointment, 'notes')),
            self::enumValue(self::value($appointment, 'status', '')),
            self::nullableString(self::value($appointment, 'appointmentDate')),
            self::nullableString(self::value($appointment, 'startTime')),
            self::nullableString(self::value($appointment, 'endTime')),
            self::nullableString(self::value($appointment, 'createdAt')),
            self::nullableString(self::value($appointment, 'updatedAt'))
        );
    }

    public static function mapToAppointmentDtos(array $appointments): array
    {
        return array_map(static fn($appointment): AppointmentDto => self::mapToAppointmentDto($appointment), $appointments);
    }

    private static function value($source, string $key, $default = null)
    {
        if (is_array($source)) {
            return $source[$key] ?? $default;
        }

        return is_object($source) ? ($source->{$key} ?? $default) : $default;
    }

    private static function enumValue($value): string
    {
        return is_object($value) && property_exists($value, 'value') ? (string) $value->value : (string) $value;
    }

    private static function nullableString($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}