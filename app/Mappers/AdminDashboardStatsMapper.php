<?php

namespace App\Mappers;

use App\DTO\AdminDashboardStatsDto;

class AdminDashboardStatsMapper
{
    public static function mapToAdminDashboardStatsDto(array $stats): AdminDashboardStatsDto
    {
        return new AdminDashboardStatsDto(
            (int) ($stats['totalProducts'] ?? 0),
            (int) ($stats['totalUsers'] ?? 0),
            (int) ($stats['totalOrders'] ?? 0),
            (int) ($stats['pendingAppointments'] ?? 0),
            (array) ($stats['recentActivities'] ?? [])
        );
    }
}