<?php

declare(strict_types=1);

namespace App\Core;

use App\Config;
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
            self::jsonResponse(self::error('Authentication is required.'), 401);
        }

        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key(Config::jwtSecret(), 'HS256'));
            self::$authUser = $decoded;
        } catch (Exception $e) {
            self::jsonResponse(self::error('Invalid or expired token'), 401);
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
            self::jsonResponse(self::error('Admin access is required.'), 403);
            exit;
        }
    }


    /**
     * Check if user is customer (not admin)
     */
    public static function requireCustomer(): void
    {
        self::requireAuth();
        $role = self::$authUser->role;

        if ($role !== 'customer') {
            self::jsonResponse(self::error('Customer access is required.'), 403);
            exit;
        }
    }

    private static function error(string $message, array $errors = [], $data = null): array
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }

    private static function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
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