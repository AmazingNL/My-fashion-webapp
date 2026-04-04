<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\RepositoryBase;
use PDOException;
use RuntimeException;

class PasswordResetTokenRepository extends RepositoryBase implements IPasswordResetTokenRepository
{
    public function invalidateAllForUser(int $userId): bool
    {
        try {
            $sql = "UPDATE password_reset_tokens SET used = 1
                    WHERE userId = :userId AND used = 0";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':userId' => $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to invalidate password reset tokens: ' . $e->getMessage());
        }
    }

    public function create(int $userId, string $token, string $codeHash, string $expiresAt): int
    {
        try {
            $sql = "INSERT INTO password_reset_tokens (userId, token, codeHash, expiresAt, used)
                    VALUES (:userId, :token, :codeHash, :expiresAt, 0)";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':userId' => $userId,
                ':token' => $token,
                ':codeHash' => $codeHash,
                ':expiresAt' => $expiresAt,
            ]);

            return (int) $this->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to create password reset token: ' . $e->getMessage());
        }
    }

    public function findValidByToken(string $token): ?array
    {
        try {
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
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to find password reset token: ' . $e->getMessage());
        }
    }

    public function markUsed(int $tokenId): bool
    {
        try {
            $sql = "UPDATE password_reset_tokens SET used = 1 WHERE tokenId = :id LIMIT 1";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':id' => $tokenId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to mark password reset token used: ' . $e->getMessage());
        }
    }
}
