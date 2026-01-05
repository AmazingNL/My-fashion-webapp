<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ActivityLog;
use App\Core\RepositoryBase;

class ActivityLogRepository extends RepositoryBase implements IActivityLogRepository
{
    public function create(ActivityLog $log): ?int
    {
        $sql = "INSERT INTO activity_logs 
                (userId, action, entityType, entityId, details, ipAddress, userAgent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            $log->getUserId(),
            $log->getAction(),
            $log->getEntityType(),
            $log->getEntityId(),
            $log->getDetails(),
            $log->getIpAddress(),
            $log->getUserAgent()
        ]);

        return (int) $this->getConnection()->lastInsertId() ?: null;
    }

    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT l.*, u.email, u.firstName, u.lastName 
                FROM activity_logs l
                LEFT JOIN users u ON l.userId = u.userId
                ORDER BY l.createdAt DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$limit, $offset]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByUser(int $userId, int $limit = 100): array
    {
        $sql = "SELECT * FROM activity_logs 
                WHERE userId = ? 
                ORDER BY createdAt DESC 
                LIMIT ?";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$userId, $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByAction(string $action, int $limit = 100): array
    {
        $sql = "SELECT l.*, u.email, u.firstName, u.lastName 
                FROM activity_logs l
                LEFT JOIN users u ON l.userId = u.userId
                WHERE l.action LIKE ?
                ORDER BY l.createdAt DESC 
                LIMIT ?";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(["%{$action}%", $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function deleteOlderThan(int $days): int
    {
        $sql = "DELETE FROM activity_logs 
                WHERE createdAt < DATE_SUB(NOW(), INTERVAL ? DAY)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$days]);

        return $stmt->rowCount();
    }
}






