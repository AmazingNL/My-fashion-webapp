<?php
namespace App\Mappers;

use App\DTO\LoginDto;

class LoginMapper
{
    public static function mapToLoginDto(array $data): LoginDto
    {
        return new LoginDto(
            (string)($data['email'] ?? ''),
            (string)($data['password'] ?? '')
        );
        
    }
}