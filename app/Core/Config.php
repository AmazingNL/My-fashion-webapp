<?php

namespace App\Core;

class Config
{
    public static function dbServerName(): string
    {
        return $_ENV['DB_SERVER_NAME'];
    }

    public static function dbUsername(): string
    {
        return $_ENV['DB_USERNAME'];
    }

    public static function dbPassword(): string
    {
        return $_ENV['DB_PASSWORD'];
    }

    public static function dbName(): string
    {
        return $_ENV['DB_NAME'];
    }

    public static function jwtSecret(): string
    {
        return $_ENV['JWT_SECRET'];
    }
}