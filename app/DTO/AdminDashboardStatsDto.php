<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class AdminDashboardStatsDto implements JsonSerializable
{
    public function __construct(
        public readonly int $totalProducts,
        public readonly int $totalUsers,
        public readonly int $totalOrders,
        public readonly int $pendingAppointments,
        public readonly array $recentActivities = []
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}