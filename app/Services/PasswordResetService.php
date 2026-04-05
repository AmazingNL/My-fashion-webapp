<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PasswordResetTokenRepository;

class PasswordResetService implements IPasswordResetService
{
    public function __construct(private PasswordResetTokenRepository $repo) {}

    public function createReset(int $userId): array
    {
        $this->repo->invalidateAllForUser($userId);

        $token = bin2hex(random_bytes(32));
        $code  = (string) random_int(100000, 999999);

        $codeHash = password_hash($code, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 15 * 60); // 15 mins

        $tokenId = $this->repo->create($userId, $token, $codeHash, $expiresAt);

        return ['tokenId' => $tokenId, 'token' => $token, 'code' => $code];
    }

    public function validate(string $token, string $code): ?array
    {
        $row = $this->repo->findValidByToken($token);
        if (!$row) return null;

        if (!password_verify($code, (string)$row['codeHash'])) return null;

        return $row;
    }

    public function markUsed(int $tokenId): void
    {
        $this->repo->markUsed($tokenId);
    }
}
