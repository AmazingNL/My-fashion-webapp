<?php

namespace App\Core;

abstract class ControllerBase
{
    private ?array $jsonInput = null;

    protected function jwtCode(): string
    {
        return Config::jwtSecret();
    }

    protected function jsonResponse($data, int $statusCode = 200): void
    {
        ApiResponse::sendAndExit($data, $statusCode);
    }

    protected function success($data = null, string $message = 'success'): array
    {
        return ApiResponse::success($data, $message);
    }

    protected function error(string $message, array $errors = [], $data = null): array
    {
        return ApiResponse::error($message, $errors, $data);
    }

    protected function currentUserId(): ?int
    {
        if (Middleware::$authUser !== null && isset(Middleware::$authUser->sub)) {
            return (int) Middleware::$authUser->sub;
        }
        return null;
    }


    protected function input(string $key, $default = null)
    {
        $json = $this->jsonInput();
        return $_POST[$key] ?? $json[$key] ?? $_GET[$key] ?? $default;
    }

    protected function requestData(): array
    {
        return array_replace_recursive($_GET, $this->jsonInput(), $_POST);
    }

    private function jsonInput(): array
    {
        if ($this->jsonInput !== null) {
            return $this->jsonInput;
        }

        $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
        if (!str_contains(strtolower($contentType), 'application/json')) {
            $this->jsonInput = [];
            return $this->jsonInput;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            $this->jsonInput = [];
            return $this->jsonInput;
        }

        $decoded = json_decode($raw, true);
        $this->jsonInput = is_array($decoded) ? $decoded : [];
        return $this->jsonInput;
    }


}
