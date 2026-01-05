<?php

namespace App\Services;

use App\Models\ActivityLog;

interface IActivityLogService
{
    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $details = null
    ): void;

    public function getAllLogs(int $limit = 100, int $offset = 0): array;
    public function getUserLogs(int $userId, int $limit = 100): array;
    public function getActionLogs(string $action, int $limit = 100): array;
    public function cleanupOldLogs(int $days = 90): int;
    public function exportLogsToFile(string $filename): string;
}