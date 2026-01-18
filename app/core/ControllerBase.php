<?php

namespace App\Core;

abstract class ControllerBase
{
    // Base controller code

    protected function render(string $view, $data = [], $layout = 'main'): void
    {
        $data['csrf'] ??= $this->csrfToken();

        extract($data, EXTR_SKIP);

        $content = __DIR__ . '/../Views/' . $view . '.php';
        $layout = __DIR__ . '/../Views/Layouts/'.$layout .'.php';

        if (!file_exists($content)) {
            throw new \Exception("view file not found: " . $view);
        }

        require $layout;
    }


    protected function redirect(string $url): void
    {
        if (headers_sent($file, $line)) {
            error_log("Redirect blocked: headers already sent in $file:$line");
            return; 
        }
        header("Location: $url");
        exit;
    }

    protected function jsonResponse($data, int $statusCode = 200): void
    {
        if (headers_sent($file, $line)) {
            error_log("JSON blocked: headers already sent in $file:$line");
            // Echo JSON without headers, or just exit
        }
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }


    protected function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }


    protected function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    protected function csrfToken(): string
    {
        $this->ensureSession();
        $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
        return $_SESSION['csrf'];
    }

protected function validateCsrf(): void
{
    $this->ensureSession();

    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $wantsJson = str_contains($accept, 'application/json');

    if (empty($_SESSION['csrf']) || empty($token) || !hash_equals($_SESSION['csrf'], $token)) {
        if ($wantsJson) {
            $this->jsonResponse([
                'errors' => ['Your session expired. Please refresh the page and try again.']
            ], 403);
        }

        http_response_code(403);
        exit('Invalid CSRF token');
    }
}


    protected function csrfField(): string
    {
        $token = htmlspecialchars($this->csrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf" value="' . $token . '">';
    }

    protected function currentUserId(): ?int
    {
        $this->ensureSession();
        return isset($_SESSION['userId']) ? (int) $_SESSION['userId'] : null;
    }


}