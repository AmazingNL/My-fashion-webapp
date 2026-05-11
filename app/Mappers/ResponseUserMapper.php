<?php
namespace App\Mappers;

use App\DTO\UserDto;
use App\Models\User;

class ResponseUserMapper
{
    public static function responseUserMapper($user): UserDto
    {
        return new UserDto(
            (string) self::value($user, 'userId', ''),
            (string) self::value($user, 'firstName', ''),
            (string) self::value($user, 'lastName', ''),
            (string) self::value($user, 'phone', ''),
            (string) self::value($user, 'role', ''),
            (string) self::value($user, 'email', ''),
            self::nullableString(self::value($user, 'createdAt'))
        );
    }

    public static function responseUserMappers(array $users): array
    {
        return array_map(static fn($user): UserDto => self::responseUserMapper($user), $users);
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