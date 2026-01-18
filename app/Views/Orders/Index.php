<?php
// $orders is passed by OrderController::index() :contentReference[oaicite:6]{index=6}
$rows = array_map(function ($o) {
    return [
        'orderId' => (int)$o->getOrderId(),
        'status' => (string)$o->getStatus(),
        'totalAmount' => (float)$o->getTotalAmount(),
        'createdAt' => (string)$o->getCreatedAt(),
        'paymentStatus' => (string)$o->getPaymentStatus(),
    ];
}, $orders ?? []);
?>
<link rel="stylesheet" href="/assets/css/orders.css">

<section class="orders">
    <header class="orders__head">
        <div>
            <h1>My Orders</h1>
            <p class="orders__sub">Track your outfits as they move from idea to doorstep 🧵</p>
        </div>

        <div class="orders__actions">
            <a class="btn btn--secondary" href="/products">Continue shopping</a>
            <button class="btn btn--ghost" id="refreshOrdersBtn" type="button">Refresh</button>
        </div>
    </header>

    <div id="ordersError" class="notice notice--error" hidden></div>
    <div id="ordersSuccess" class="notice notice--success" hidden></div>

    <div class="card orders__toolbar">
        <div class="field">
            <label for="statusFilter">Filter by status</label>
            <select id="statusFilter">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="paid">Completed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="field">
            <label for="searchOrders">Search</label>
            <input id="searchOrders" type="text" placeholder="Order #, status, payment..." />
        </div>
    </div>

    <div id="ordersGrid" class="grid grid--3"></div>

    <div id="ordersEmpty" class="empty-state" hidden>
        <div class="empty-state__icon">🧺</div>
        <div class="empty-state__text">No orders yet</div>
        <a class="btn btn--primary" href="/products">Browse products</a>
    </div>
</section>

<script id="ordersData" type="application/json">
<?= json_encode(['orders' => $rows], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>



<script src="/assets/js/orders.js"></script>
