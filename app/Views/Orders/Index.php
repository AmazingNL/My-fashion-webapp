<?php
$orders = $orders ?? [];
$statusFilter = (string) ($statusFilter ?? '');
$search = (string) ($search ?? '');
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');

$canCancel = static function (string $status): bool {
    $normalized = strtolower(trim($status));
    return in_array($normalized, ['pending', 'processing', 'paid'], true);
};
?>

<section class="orders">
    <header class="orders__head">
        <div>
            <h1>My Orders</h1>
            <p class="orders__sub">Track your outfits as they move from idea to doorstep 🧵</p>
        </div>

        <div class="orders__actions">
            <a class="btn btn--secondary" href="/productLists">Continue shopping</a>
            <a class="btn btn--ghost" href="/orders">Refresh</a>
        </div>
    </header>

    <?php if ($error !== ''): ?>
        <div class="notice notice--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="notice notice--success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <?php require __DIR__ . '/partials/FilterForm.php'; ?>
    <?php endif; ?>

    <?php require __DIR__ . '/partials/OrderGrid.php'; ?>

</section>
