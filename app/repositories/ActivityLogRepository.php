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
        $limit = max(1, (int) $limit);
        $offset = max(0, (int) $offset);

        $sql = "SELECT l.*, u.email, u.firstName, u.lastName 
            FROM activity_logs l
            LEFT JOIN users u ON l.userId = u.userId
            ORDER BY l.createdAt DESC
            LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
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
    public function getFiltered(
        ?int $userId,
        ?string $action,
        ?string $dateFrom,
        ?string $dateTo,
        int $limit = 100,
        int $offset = 0
    ): array {
        $where = [];
        $params = [];

        if ($userId !== null) {
            $where[] = "l.userId = ?";
            $params[] = $userId;
        }

        if ($action !== null && $action !== '') {
            $where[] = "l.action LIKE ?";
            $params[] = "%{$action}%";
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = "l.createdAt >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== null && $dateTo !== '') {
            $where[] = "l.createdAt <= ?";
            $params[] = $dateTo;
        }

        // IMPORTANT: inject numeric limit/offset (MariaDB + emulated prepares issue)
        $limit = max(1, (int) $limit);
        $offset = max(0, (int) $offset);

        $sql = "
        SELECT l.*, u.email, u.firstName, u.lastName
        FROM activity_logs l
        LEFT JOIN users u ON u.userId = l.userId
    ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY l.createdAt DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }


    public function countFiltered(
        ?int $userId,
        ?string $action,
        ?string $dateFrom,
        ?string $dateTo
    ): int {
        $where = [];
        $params = [];

        if ($userId !== null) {
            $where[] = "userId = ?";
            $params[] = $userId;
        }

        if ($action !== null && $action !== '') {
            $where[] = "action LIKE ?";
            $params[] = "%{$action}%";
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = "createdAt >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== null && $dateTo !== '') {
            $where[] = "createdAt <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT COUNT(*) FROM activity_logs";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function deleteAll(): int
    {
        // TRUNCATE is fastest but returns 0 rowCount usually; DELETE returns count.
        $stmt = $this->getConnection()->prepare("DELETE FROM activity_logs");
        $stmt->execute();
        return $stmt->rowCount();
    }

}






