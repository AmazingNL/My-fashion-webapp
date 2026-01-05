<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Repositories\IActivityLogRepository;

class ActivityLogService implements IActivityLogService
{
    private IActivityLogRepository $logRepository;

    public function __construct(IActivityLogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Log an activity
     */
    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $details = null
    ): void {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $log = new ActivityLog(
            null,
            $userId,
            $action,
            $entityType,
            $entityId,
            $details,
            $ipAddress,
            $userAgent
        );

        $this->logRepository->create($log);
    }

    /**
     * Get all logs with pagination
     */
    public function getAllLogs(int $limit = 100, int $offset = 0): array
    {
        return $this->logRepository->getAll($limit, $offset);
    }

    /**
     * Get logs by user
     */
    public function getUserLogs(int $userId, int $limit = 100): array
    {
        return $this->logRepository->getByUser($userId, $limit);
    }

    /**
     * Get logs by action
     */
    public function getActionLogs(string $action, int $limit = 100): array
    {
        return $this->logRepository->getByAction($action, $limit);
    }

    /**
     * Clean up old logs
     */
    public function cleanupOldLogs(int $days = 90): int
    {
        return $this->logRepository->deleteOlderThan($days);
    }

    /**
     * Export logs to file for admin
     */
    public function exportLogsToFile(string $filename): string
    {
        if ($filename === null) {
            $filename = 'activity_logs_' . date('Y-m-d_His') . '.txt';
        }

        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $filepath = $logDir . '/' . $filename;
        $logs = $this->getAllLogs(1000); // Get last 1000 logs

        $content = "Activity Logs Export - " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("=", 80) . "\n\n";

        foreach ($logs as $log) {
            $content .= "[{$log['createdAt']}] ";
            $content .= "User: " . ($log['email'] ?? 'N/A') . " ";
            $content .= "({$log['firstName']} {$log['lastName']}) | ";
            $content .= "Action: {$log['action']}";
            
            if ($log['entityType']) {
                $content .= " | Entity: {$log['entityType']}";
                if ($log['entityId']) {
                    $content .= " ID:{$log['entityId']}";
                }
            }
            
            if ($log['details']) {
                $content .= " | Details: {$log['details']}";
            }
            
            $content .= " | IP: {$log['ipAddress']}";
            $content .= "\n";
        }

        file_put_contents($filepath, $content);

        return $filepath;
    }
}