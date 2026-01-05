<link rel="stylesheet" href="/assets/css/orderDetails.css">

<section class="orderDetails">
    <header class="orderDetails__head">
        <div>
            <h1>Order #<?= htmlspecialchars((string) $order->getOrderId(), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="orderDetails__sub">
                Status: <strong><?= htmlspecialchars((string) $order->getStatus(), ENT_QUOTES, 'UTF-8') ?></strong>
                · Payment:
                <strong><?= htmlspecialchars((string) $order->getPaymentStatus(), ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
        </div>

        <div class="orderDetails__actions">
            <a class="btn btn--ghost" href="/orders">Back</a>
            <a class="btn btn--secondary" href="/products">Shop more</a>
        </div>
    </header>

    <div class="grid grid--2">
        <div class="card">
            <h2>Summary</h2>
            <div class="orderDetails__kv">
                <div><span>Total</span><strong
                        id="orderTotal"><?= htmlspecialchars((string) $order->getTotalAmount(), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div>
                    <span>Created</span><strong><?= htmlspecialchars((string) $order->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Addresses</h2>
            <div class="orderDetails__addr">
                <div>
                    <div class="orderDetails__label">Shipping</div>
                    <div><?= nl2br(htmlspecialchars((string) $order->getShippingAddress(), ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
                <div>
                    <div class="orderDetails__label">Billing</div>
                    <div><?= nl2br(htmlspecialchars((string) $order->getBillingAddress(), ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card orderDetails__itemsCard">
        <div class="orderDetails__itemsTop">
            <h2>Items</h2>
            <button class="btn btn--ghost" id="refreshItemsBtn" type="button">Refresh</button>
        </div>

        <div id="itemsError" class="notice notice--error" hidden></div>

        <div id="itemsList" class="orderDetails__itemsList"></div>

        <div id="itemsEmpty" class="empty-state" hidden>
            <div class="empty-state__icon">🧾</div>
            <div class="empty-state__text">No items found for this order</div>
        </div>

        <div class="orderDetails__itemsFooter">
            <span>Items total</span>
            <strong id="itemsTotal">€0.00</strong>
        </div>
    </div>
</section>

<script id="orderDetailsData" type="application/json">
<?= json_encode(
    ['orderId' => (int) $order->getOrderId()],
    JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?>
</script>


<script src="/assets/js/orderDetails.js"></script>