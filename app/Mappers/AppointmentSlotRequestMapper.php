<?php

namespace App\Mappers;

use App\DTO\AppointmentSlotRequestDto;

class AppointmentSlotRequestMapper
{
    public static function mapToAppointmentSlotRequestDto(array $data): AppointmentSlotRequestDto
    {
        return new AppointmentSlotRequestDto(
            (string) ($data['appointmentDate'] ?? ''),
            (string) ($data['startTime'] ?? ''),
            (string) ($data['endTime'] ?? ''),
            (string) ($data['bulkMonth'] ?? '') === '1',
            (string) ($data['secondStartTime'] ?? ''),
            (string) ($data['secondEndTime'] ?? '')
        );
    }
}