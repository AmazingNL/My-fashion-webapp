<?php

namespace App\Mappers;

use App\DTO\AppointmentRequestDto;

class AppointmentRequestMapper
{
    public static function mapToAppointmentRequestDto(array $data): AppointmentRequestDto
    {
        return new AppointmentRequestDto(
            (int) ($data['slotId'] ?? 0),
            trim((string) ($data['designType'] ?? '')) ?: null,
            trim((string) ($data['notes'] ?? '')) ?: null,
            (string) ($data['status'] ?? '')
        );
    }
}