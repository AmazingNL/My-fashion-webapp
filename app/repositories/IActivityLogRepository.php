<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ActivityLog;
use App\Core\RepositoryBase;

interface IActivityLogRepository
{
    public function create(ActivityLog $log): ?int;
    public function getAll(int $limit = 100, int $offset = 0): array;
    public function getByUser(int $userId, int $limit = 100): array;
    public function getByAction(string $action, int $limit = 100): array;
    public function deleteOlderThan(int $days): int;
    public function getFiltered(
        ?int $userId,
        ?string $action,
        ?string $dateFrom,
        ?string $dateTo,
        int $limit = 100,
        int $offset = 0
    ): array;

    public function countFiltered(
        ?int $userId,
        ?string $action,
        ?string $dateFrom,
        ?string $dateTo
    ): int;

    public function deleteAll(): int;

}