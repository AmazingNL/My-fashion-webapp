<?php

declare(strict_types=1);

namespace App\Services;

interface IPasswordResetService
{
    /**
     * Creates token+code for a user.
     * Returns: ['tokenId' => int, 'token' => string, 'code' => string]
     */
    public function createReset(int $userId): array;

    /**
     * Validates token+code.
     * Returns DB row if valid, otherwise null.
     */
    public function validate(string $token, string $code): ?array;

    public function markUsed(int $tokenId): void;
}
