<?php

namespace App\Services;


interface IActivityLogService
{
    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $details = null
    ): ?int;

    public function getLogs(
        int $limit = 50,
        int $offset = 0,
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array;
    public function countLogs(
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int;
    public function clearAll(): int;
    public function purgeOlderThan(int $days): int;
    public function exportLogsToFile(
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): string;

}