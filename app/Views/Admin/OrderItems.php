<?php

/**
 * Order Items - Admin page
 * Display line items and totals for a specific order
 */

/** @var \App\Models\Order $order */
/** @var array $items */

$id = (int) ($order->orderId ?? 0);
$status = strtolower((string) ($order->status?->value ?? 'pending'));

$itemsTotal = 0.0;
foreach ($items as $it) {
    $itemsTotal += ((int) ($it['quantity'] ?? 0) * (float) ($it['price'] ?? 0));
}

?>

<?php require __DIR__ . '/partials/order-items/Header.php'; ?>

<div class="orderItemsPage">
    <?php require __DIR__ . '/partials/order-items/ItemsTable.php'; ?>
    <?php require __DIR__ . '/partials/order-items/Summary.php'; ?>
</div>
