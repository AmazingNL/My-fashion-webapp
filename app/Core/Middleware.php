<?php

declare(strict_types=1);

namespace App\Core;

class Middleware
{
    /**
     * Check if user is authenticated
     */
    public static function requireAuth(): void
    {
        if (!isset($_SESSION['userId'])) {
            header('Location: /?error=login_required');
            exit;
        }
    }

    /**
     * Check if user is admin
     */
    public static function requireAdmin(): void
    {
        // Make sure a session exists
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Must be logged in first
        self::requireAuth();

        $role = $_SESSION['role'] ?? 'customer';

        if ($role !== 'admin') {
            http_response_code(403);
            header('Location: /productLists?error=forbidden');
            exit;
        }
    }


    /**
     * Check if user is customer (not admin)
     */
    public static function requireCustomer(): void
    {
        self::requireAuth();

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
            http_response_code(403);
            header('Location: /admin/dashboard?error=access_denied');
            exit;
        }
    }
}