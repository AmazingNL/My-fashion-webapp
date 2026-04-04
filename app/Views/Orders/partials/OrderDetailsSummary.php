<header class="orderDetails__head">
    <div>
        <h1>Order #<?= htmlspecialchars((string) $orderId, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="orderDetails__sub">
            Status: <strong><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></strong>
            · Payment:
            <strong><?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
    </div>

    <div class="orderDetails__actions">
        <a class="btn btn--ghost" href="/orders">Back</a>
        <a class="btn btn--secondary" href="/productLists">Shop more</a>
    </div>
</header>

<div class="grid grid--2">
    <div class="card">
        <h2>Summary</h2>
        <div class="orderDetails__kv">
            <div><span>Total</span><strong
                    id="orderTotal">€<?= htmlspecialchars(number_format($totalAmount, 2), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div>
                <span>Created</span><strong><?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Addresses</h2>
        <div class="orderDetails__addr">
            <div>
                <div class="orderDetails__label">Shipping</div>
                <div><?= nl2br(htmlspecialchars($shipping, ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
            <div>
                <div class="orderDetails__label">Billing</div>
                <div><?= nl2br(htmlspecialchars($billing, ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
        </div>
    </div>
</div>