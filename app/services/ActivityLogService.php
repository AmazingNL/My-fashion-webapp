<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Repositories\IActivityLogRepository;

class ActivityLogService
{
    public function __construct(
        private readonly IActivityLogRepository $repo
    ) {}

    /**
     * Record a log entry.
     */
    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $details = null
    ): ?int {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $log = new ActivityLog(
            null,
            $userId,
            $action,
            $entityType,
            $entityId,
            $details,
            $ip,
            $ua,
            null
        );

        return $this->repo->create($log);
    }

    /**
     * Admin list with filters + pagination.
     */
    public function getLogs(
        int $limit = 50,
        int $offset = 0,
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null, // 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'
        ?string $dateTo = null
    ): array {
        return $this->repo->getFiltered($userId, $action, $dateFrom, $dateTo, $limit, $offset);
    }

    public function countLogs(
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        return $this->repo->countFiltered($userId, $action, $dateFrom, $dateTo);
    }

    /**
     * Delete logs older than X days.
     */
    public function purgeOlderThan(int $days): int
    {
        $days = max(1, $days);
        return $this->repo->deleteOlderThan($days);
    }

    /**
     * Clear everything (admin-only).
     */
    public function clearAll(): int
    {
        return $this->repo->deleteAll();
    }

    /**
     * Export filtered logs to a CSV file and return filepath.
     */
    public function exportLogsToFile(
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): string {
        $rows = $this->repo->getFiltered($userId, $action, $dateFrom, $dateTo, 5000, 0);

        $dir = sys_get_temp_dir();
        $filename = 'activity_logs_' . date('Ymd_His') . '.csv';
        $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        $fp = fopen($path, 'w');
        if (!$fp) {
            throw new \RuntimeException('Could not create export file');
        }

        // Header
        fputcsv($fp, [
            'logId', 'createdAt', 'userId', 'userEmail',
            'action', 'entityType', 'entityId', 'details', 'ipAddress'
        ]);

        foreach ($rows as $r) {
            fputcsv($fp, [
                $r['logId'] ?? '',
                $r['createdAt'] ?? '',
                $r['userId'] ?? '',
                $r['email'] ?? '',
                $r['action'] ?? '',
                $r['entityType'] ?? '',
                $r['entityId'] ?? '',
                $r['details'] ?? '',
                $r['ipAddress'] ?? '',
            ]);
        }

        fclose($fp);
        return $path;
    }
}
