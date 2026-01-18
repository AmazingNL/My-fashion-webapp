<?php
/** @var \App\Models\Order $order */

$id = (int) $order->getOrderId();
$status = strtolower((string) $order->getStatus());
$total = (float) $order->getTotalAmount();
$created = (string) $order->getCreatedAt();
$payment = (string) $order->getPaymentMethod();

$shipping = (string) $order->getShippingAddress();
$billing = (string) $order->getBillingAddress();
$userId = (int) $order->getUserId();
?>

<link rel="stylesheet" href="/assets/css/adminManageOrders.css">

<div class="admin-shell">

    <div class="admin-hero admin-hero--compact">
        <div class="admin-hero__left">
            <h1 class="admin-hero__title">Order <span class="muted">#<?= $id ?></span></h1>
            <p class="admin-hero__subtitle">Overview, payment, addresses, and quick actions.</p>
        </div>

        <div class="admin-hero__right">
            <span class="statusPill" data-status="<?= htmlspecialchars($status) ?>">
                <?= htmlspecialchars($status) ?>
            </span>
        </div>
    </div>

    <div class="orderOpenPage">

        <!-- LEFT -->
        <section class="orderOpenCard">
            <header class="orderOpenHeader">
                <div>
                    <h2 class="orderOpenTitle">Order Summary</h2>
                    <p class="orderOpenHint">Core information about this order.</p>
                </div>

                <div class="orderTotalPill">
                    <div class="orderTotalPill__k">Total</div>
                    <div class="orderTotalPill__v">€<?= htmlspecialchars(number_format($total, 2)) ?></div>
                </div>
            </header>

            <div class="orderKV">
                <div class="kv">
                    <div class="k">Order ID</div>
                    <div class="v mono">#<?= $id ?></div>
                </div>

                <div class="kv">
                    <div class="k">Customer ID</div>
                    <div class="v mono">#<?= $userId ?></div>
                </div>

                <div class="kv">
                    <div class="k">Created</div>
                    <div class="v"><?= htmlspecialchars($created) ?></div>
                </div>

                <div class="kv">
                    <div class="k">Payment Method</div>
                    <div class="v"><?= htmlspecialchars($payment) ?></div>
                </div>
            </div>

            <div class="orderMiniActions">
                <a class="orderMiniBtn" href="/admin/orders/<?= $id ?>/items">View Items</a>
                <a class="orderMiniBtn orderMiniBtn--ghost" href="/admin/orders">Back to Orders</a>
            </div>
        </section>

        <!-- RIGHT -->
        <aside class="orderAddressCard">
            <h2 class="orderAddressTitle">Addresses</h2>

            <div class="addressStack">
                <div class="addrCard">
                    <div class="addrTitle">Shipping</div>
                    <div class="addrBody"><?= nl2br(htmlspecialchars($shipping)) ?></div>
                </div>

                <div class="addrCard">
                    <div class="addrTitle">Billing</div>
                    <div class="addrBody"><?= nl2br(htmlspecialchars($billing)) ?></div>
                </div>
            </div>
        </aside>

    </div>

</div>