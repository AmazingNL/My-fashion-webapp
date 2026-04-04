<?php
/** @var \App\Models\Order $order */
/** @var array $items */

$orderId = (int) ($order->orderId ?? 0);
$status = (string) ($order->status?->value ?? 'unknown');
$paymentStatus = (string) ($order->paymentStatus?->value ?? 'pending');
$totalAmount = (float) ($order->totalAmount ?? 0);
$createdAt = (string) ($order->createdAt ?? '-');
$shipping = (string) ($order->shippingAddress ?? '');
$billing = (string) ($order->billingAddress ?? '');
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');

$itemsTotal = 0.0;
foreach (($items ?? []) as $item) {
    $itemsTotal += ((int) ($item['quantity'] ?? 0) * (float) ($item['price'] ?? 0));
}
?>

<section class="orderDetails">
    <?php if ($error !== ''): ?>
        <div class="notice notice--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <div class="notice notice--success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/partials/OrderDetailsSummary.php'; ?>

    <?php require __DIR__ . '/partials/OrderDetailsItems.php'; ?>
</section>