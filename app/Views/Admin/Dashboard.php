<?php
/** @var array $stats */
$stats = $stats ?? [];

$totalProducts = is_array($stats['totalProducts'] ?? null) ? count($stats['totalProducts']) : (int)($stats['totalProducts'] ?? 0);
$totalUsers    = (int)($stats['totalUsers'] ?? 0);
$totalOrders   = (int)($stats['totalOrders'] ?? 0);
$pendingAppts  = (int)($stats['pendingAppointments'] ?? 0);

?>

<section class="admin-shell">
    <?php require __DIR__ . '/partials/DashboardHero.php'; ?>
    <?php require __DIR__ . '/partials/DashboardCards.php'; ?>
</section>
