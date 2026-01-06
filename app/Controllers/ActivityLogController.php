<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\Middleware;
use App\Services\ActivityLogService;

class ActivityLogController extends ControllerBase
{
    public function __construct(
        private readonly ActivityLogService $logService
    ) {}

    /**
     * Admin page (render a view you create: Views/Admin/ActivityLogs.php)
     */
    public function adminIndex(): void
    {
        Middleware::requireAdmin();

        $page = max(1, (int)$this->input('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $userId = $this->input('userId');
        $userId = ($userId !== null && $userId !== '') ? (int)$userId : null;

        $action = trim((string)$this->input('action', ''));
        $action = $action !== '' ? $action : null;

        $from = trim((string)$this->input('from', ''));
        $from = $from !== '' ? $from : null;

        $to = trim((string)$this->input('to', ''));
        $to = $to !== '' ? $to : null;

        $logs = $this->logService->getLogs($limit, $offset, $userId, $action, $from, $to);
        $total = $this->logService->countLogs($userId, $action, $from, $to);
        $pages = (int)ceil($total / $limit);

        $this->render('Admin/ActivityLogs', [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'page' => $page,
            'pages' => max(1, $pages),
            'total' => $total,
            'filters' => [
                'userId' => $userId,
                'action' => $action,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    /**
     * JSON endpoint for AJAX filters/pagination
     * GET /admin/activity-logs/api?userId=&action=&from=&to=&page=
     */
    public function apiList(): void
    {
        Middleware::requireAdmin();

        $page = max(1, (int)$this->input('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $userId = $this->input('userId');
        $userId = ($userId !== null && $userId !== '') ? (int)$userId : null;

        $action = trim((string)$this->input('action', ''));
        $action = $action !== '' ? $action : null;

        $from = trim((string)$this->input('from', ''));
        $from = $from !== '' ? $from : null;

        $to = trim((string)$this->input('to', ''));
        $to = $to !== '' ? $to : null;

        $logs = $this->logService->getLogs($limit, $offset, $userId, $action, $from, $to);
        $total = $this->logService->countLogs($userId, $action, $from, $to);

        $this->jsonResponse([
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'logs' => $logs,
        ]);
    }

    /**
     * POST /admin/activity-logs/export
     * Returns a CSV download (no view).
     */
    public function export(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $userId = $this->input('userId');
        $userId = ($userId !== null && $userId !== '') ? (int)$userId : null;

        $action = trim((string)$this->input('action', ''));
        $action = $action !== '' ? $action : null;

        $from = trim((string)$this->input('from', ''));
        $from = $from !== '' ? $from : null;

        $to = trim((string)$this->input('to', ''));
        $to = $to !== '' ? $to : null;

        $filepath = $this->logService->exportLogsToFile($userId, $action, $from, $to);

        // log this action too
        $this->logService->log(
            $this->currentUserId(),
            'Exported Activity Logs',
            'activity_logs',
            null,
            'Exported logs to CSV'
        );

        if (!is_file($filepath)) {
            $this->jsonResponse(['error' => 'File not found'], 404);
        }

        // IMPORTANT: ensure no output before headers
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    /**
     * POST /admin/activity-logs/purge (days)
     */
    public function purge(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $days = (int)$this->input('days', 30);
        $deleted = $this->logService->purgeOlderThan($days);

        $this->logService->log(
            $this->currentUserId(),
            'Purged Activity Logs',
            'activity_logs',
            null,
            "Deleted {$deleted} logs older than {$days} days"
        );

        $this->jsonResponse(['deleted' => $deleted]);
    }

    /**
     * POST /admin/activity-logs/clear
     */
    public function clear(): void
    {
        Middleware::requireAdmin();
        $this->validateCsrf();

        $deleted = $this->logService->clearAll();

        $this->logService->log(
            $this->currentUserId(),
            'Cleared Activity Logs',
            'activity_logs',
            null,
            "Cleared logs (affected rows: {$deleted})"
        );

        $this->jsonResponse(['deleted' => $deleted]);
    }
}
