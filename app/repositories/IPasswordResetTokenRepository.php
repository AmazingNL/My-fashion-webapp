<?php

declare(strict_types=1);

namespace App\Repositories;

interface IPasswordResetTokenRepository
{
    public function invalidateAllForUser(int $userId): bool;

    public function create(int $userId, string $token, string $codeHash, string $expiresAt): int;

    public function findValidByToken(string $token): ?array;

    public function markUsed(int $tokenId): bool;
}
