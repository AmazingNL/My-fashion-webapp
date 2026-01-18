<?php
/**
 * Admin Activity Logs view
 * Expects: $logs (array)
 * Optional: $page / $currentPage, $pages, $total, $filters
 */

$logs = $logs ?? [];

$page  = (int)($page ?? ($currentPage ?? 1));
$pages = (int)($pages ?? 1);
$total = (int)($total ?? count($logs));

$filters = $filters ?? [
    'userId' => $userId ?? null,
    'action' => $action ?? null,
    'from'   => $from ?? null,
    'to'     => $to ?? null,
];

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// Build pagination links while keeping filters
$baseParams = array_filter([
    'userId' => $filters['userId'] ?? null,
    'action' => $filters['action'] ?? null,
    'from'   => $filters['from'] ?? null,
    'to'     => $filters['to'] ?? null,
], static fn($v) => $v !== null && $v !== '');

$pageUrl = static function (int $p) use ($baseParams): string {
    $params = $baseParams + ['page' => $p];
    return '/admin/activity?' . http_build_query($params);
};
?>

<link rel="stylesheet" href="/assets/css/main.css">

<main class="shell">
    <section class="panel">
        <header class="store__top">
            <div>
                <h1>Activity Logs</h1>
                <p class="muted">System activity and audit trail.</p>
            </div>
        </header>

        <!-- Filters (simple, server-side) -->
        <section class="card" style="margin-bottom: 1.25rem;">
            <form method="get" action="/admin/activity" class="grid grid--4" style="align-items:end;">
                <label class="field">
                    <span>User ID</span>
                    <input name="userId" inputmode="numeric" value="<?= $h($filters['userId'] ?? '') ?>" placeholder="e.g. 1">
                </label>

                <label class="field">
                    <span>Action contains</span>
                    <input name="action" value="<?= $h($filters['action'] ?? '') ?>" placeholder="e.g. Viewed">
                </label>

                <label class="field">
                    <span>From (YYYY-MM-DD)</span>
                    <input name="from" value="<?= $h($filters['from'] ?? '') ?>" placeholder="2026-01-01">
                </label>

                <label class="field">
                    <span>To (YYYY-MM-DD)</span>
                    <input name="to" value="<?= $h($filters['to'] ?? '') ?>" placeholder="2026-01-31">
                </label>

                <div style="grid-column: 1 / -1; display:flex; gap: .75rem; flex-wrap:wrap;">
                    <button class="btn btn--primary" type="submit">Filter</button>
                    <a class="btn btn--ghost" href="/admin/activity">Reset</a>
                </div>
            </form>
        </section>

        <?php if (empty($logs)): ?>
            <section class="card">
                <div class="empty-state">
                    <div class="empty-state__icon">📋</div>
                    <div class="empty-state__text">No activity logs</div>
                    <p class="muted">Activity logs will appear here.</p>
                </div>
            </section>
        <?php else: ?>
            <section class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
                    <div class="muted">
                        Showing page <strong><?= $page ?></strong> of <strong><?= max(1, $pages) ?></strong>
                        <span style="opacity:.75;">(<?= $total ?> total)</span>
                    </div>
                    <a class="btn btn--ghost" href="/admin/exportLogs">Export</a>
                </div>

                <div style="overflow:auto; margin-top: 1rem;">
                    <table style="width:100%; border-collapse: collapse; min-width: 820px;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:.75rem; border-bottom:1px solid var(--border-light);">When</th>
                                <th style="text-align:left; padding:.75rem; border-bottom:1px solid var(--border-light);">Action</th>
                                <th style="text-align:left; padding:.75rem; border-bottom:1px solid var(--border-light);">User</th>
                                <th style="text-align:left; padding:.75rem; border-bottom:1px solid var(--border-light);">Entity</th>
                                <th style="text-align:left; padding:.75rem; border-bottom:1px solid var(--border-light);">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $row): ?>
                                <tr>
                                    <td style="padding:.75rem; border-bottom:1px solid var(--border-light); white-space:nowrap;">
                                        <?= $h($row['createdAt'] ?? '') ?>
                                    </td>
                                    <td style="padding:.75rem; border-bottom:1px solid var(--border-light); font-weight:700;">
                                        <?= $h($row['action'] ?? 'Activity') ?>
                                    </td>
                                    <td style="padding:.75rem; border-bottom:1px solid var(--border-light);">
                                        <?= $h($row['userId'] ?? '') ?>
                                    </td>
                                    <td style="padding:.75rem; border-bottom:1px solid var(--border-light);">
                                        <?= $h($row['entityType'] ?? '') ?>
                                        <?php if (!empty($row['entityId'])): ?>
                                            <span class="pill" style="margin-left:.5rem;">#<?= $h($row['entityId']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:.75rem; border-bottom:1px solid var(--border-light);">
                                        <span class="muted"><?= $h($row['details'] ?? '') ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pages > 1): ?>
                    <nav style="display:flex; justify-content:center; gap:.75rem; margin-top:1.25rem; flex-wrap:wrap;" aria-label="Activity log pagination">
                        <a class="btn btn--ghost" href="<?= $h($pageUrl(max(1, $page - 1))) ?>" <?= $page <= 1 ? 'aria-disabled="true" style="pointer-events:none; opacity:.6;"' : '' ?>>Prev</a>
                        <span class="pill pill--primary" style="align-self:center;">Page <?= $page ?> / <?= $pages ?></span>
                        <a class="btn btn--ghost" href="<?= $h($pageUrl(min($pages, $page + 1))) ?>" <?= $page >= $pages ? 'aria-disabled="true" style="pointer-events:none; opacity:.6;"' : '' ?>>Next</a>
                    </nav>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </section>
</main>
