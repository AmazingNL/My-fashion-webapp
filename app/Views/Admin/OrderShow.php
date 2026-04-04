<?php

/**
 * Order Show - Admin page
 * Display core order details and quick actions
 */

/** @var \App\Models\Order $order */

$id = (int) ($order->orderId ?? 0);
$status = strtolower((string) ($order->status?->value ?? 'pending'));
$total = (float) ($order->totalAmount ?? 0);
$created = (string) ($order->createdAt ?? '');
$payment = (string) ($order->paymentMethod ?? '');

$shipping = (string) ($order->shippingAddress ?? '');
$billing = (string) ($order->billingAddress ?? '');
$userId = (int) ($order->userId ?? 0);

?>

<div class="admin-shell">
    <?php require __DIR__ . '/partials/order-show/Header.php'; ?>

    <div class="orderOpenPage">
        <?php require __DIR__ . '/partials/order-show/Summary.php'; ?>
        <?php require __DIR__ . '/partials/order-show/Addresses.php'; ?>
    </div>
</div>