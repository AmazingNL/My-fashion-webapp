<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\RepositoryBase;
use App\Repositories\IPasswordResetTokenRepository;

class PasswordResetTokenRepository extends RepositoryBase implements IPasswordResetTokenRepository
{
    public function invalidateAllForUser(int $userId): void
    {
        $sql = "UPDATE password_reset_tokens SET used = 1
                WHERE userId = :userId AND used = 0";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':userId' => $userId]);
    }

    public function create(int $userId, string $token, string $codeHash, string $expiresAt): int
    {
        $sql = "INSERT INTO password_reset_tokens (userId, token, codeHash, expiresAt, used)
                VALUES (:userId, :token, :codeHash, :expiresAt, 0)";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':userId' => $userId,
            ':token' => $token,
            ':codeHash' => $codeHash,
            ':expiresAt' => $expiresAt,
        ]);

        return (int)$this->getConnection()->lastInsertId();
    }

    public function findValidByToken(string $token): ?array
    {
        $sql = "SELECT * FROM password_reset_tokens
                WHERE token = :token
                  AND used = 0
                  AND expiresAt > NOW()
                ORDER BY createdAt DESC
                LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':token' => $token]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markUsed(int $tokenId): void
    {
        $sql = "UPDATE password_reset_tokens SET used = 1 WHERE tokenId = :id LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $tokenId]);
    }
}
