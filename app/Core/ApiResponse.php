<?php

declare(strict_types=1);

namespace App\Core;

// Single source of truth for the JSON envelope used across the API.
final class ApiResponse
{
    public static function success($data = null, string $message = 'success'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error(string $message, array $errors = [], $data = null): array
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

    // Write the JSON response (does not stop execution).
    public static function send($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }

    // Write the JSON response and stop execution.
    public static function sendAndExit($data, int $statusCode = 200): void
    {
        self::send($data, $statusCode);
        exit;
    }
}
