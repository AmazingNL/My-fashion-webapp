<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Middleware
{
    /**
     * Check if user is authenticated
     */
    public static ?object $authUser = null;
    public static function requireAuth(): void
    {
        $authHeader = self::authorizationHeader();

        if ($authHeader === '' || !preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
            ApiResponse::sendAndExit(ApiResponse::error('Authentication is required.'), 401);
        }

        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key(Config::jwtSecret(), 'HS256'));
            self::$authUser = $decoded;
        } catch (Exception $e) {
            ApiResponse::sendAndExit(ApiResponse::error('Invalid or expired token'), 401);
        }
    }

    /**
     * Check if user is admin
     */
    public static function requireAdmin(): void
    {
        // Must be logged in first
        self::requireAuth();

        $role = self::$authUser->role;
        if ($role !== 'admin') {
            ApiResponse::sendAndExit(ApiResponse::error('Admin access is required.'), 403);
        }
    }


    /**
     * Check if user is customer and not admin
     */
    public static function requireCustomer(): void
    {
        self::requireAuth();
        $role = self::$authUser->role;

        if ($role !== 'customer') {
            ApiResponse::sendAndExit(ApiResponse::error('Customer access is required.'), 403);
        }
    }

    private static function authorizationHeader(): string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach ($headers as $name => $value) {
            if (strtolower((string) $name) === 'authorization') {
                return trim((string) $value);
            }
        }

        return trim((string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
    }

}