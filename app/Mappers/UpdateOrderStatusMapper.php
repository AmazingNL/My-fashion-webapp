<?php

namespace App\Mappers;

use App\DTO\UpdateOrderStatusDto;

class UpdateOrderStatusMapper
{
    public static function mapToUpdateOrderStatusDto(array $data): UpdateOrderStatusDto
    {
        return new UpdateOrderStatusDto(strtolower(trim((string) ($data['status'] ?? ''))));
    }
}