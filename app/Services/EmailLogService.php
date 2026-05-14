<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class EmailLogService
{
    private string $emailDir;

    public function __construct(?string $emailDir = null)
    {
        $this->emailDir = $emailDir ?? dirname(__DIR__, 2) . '/storage/emails';
    }

    public function listEmails(): array
    {
        if (!is_dir($this->emailDir)) {
            return [];
        }

        $files = glob($this->emailDir . '/*.html') ?: [];
        rsort($files);

        return array_map(function (string $path): array {
            $raw = (string) file_get_contents($path);
            [$headers] = $this->splitHeadersAndBody($raw);

            return [
                'fileName' => basename($path),
                'to' => $this->headerValue($headers, 'To'),
                'subject' => $this->headerValue($headers, 'Subject'),
                'createdAt' => date('Y-m-d H:i:s', (int) filemtime($path)),
                'size' => filesize($path) ?: 0,
            ];
        }, $files);
    }

    public function getEmail(string $fileName): array
    {
        $safeName = basename($fileName);
        if ($safeName === '' || $safeName !== $fileName || !preg_match('/^[A-Za-z0-9_.-]+\.html$/', $safeName)) {
            throw new RuntimeException('Invalid email file.');
        }

        $path = $this->emailDir . '/' . $safeName;
        if (!is_file($path)) {
            throw new RuntimeException('Email not found.');
        }

        $raw = (string) file_get_contents($path);
        [$headers, $body] = $this->splitHeadersAndBody($raw);

        return [
            'fileName' => $safeName,
            'to' => $this->headerValue($headers, 'To'),
            'subject' => $this->headerValue($headers, 'Subject'),
            'createdAt' => date('Y-m-d H:i:s', (int) filemtime($path)),
            'size' => filesize($path) ?: 0,
            'html' => $body,
            'raw' => $raw,
        ];
    }

    private function splitHeadersAndBody(string $raw): array
    {
        $parts = preg_split("/\R\R/", $raw, 2);
        return [$parts[0] ?? '', $parts[1] ?? $raw];
    }

    private function headerValue(string $headers, string $name): string
    {
        if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+)$/mi', $headers, $matches)) {
            return trim((string) $matches[1]);
        }

        return '';
    }
}
