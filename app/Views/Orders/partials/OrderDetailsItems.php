<div class="card orderDetails__itemsCard">
    <div class="orderDetails__itemsTop">
        <h2>Items</h2>
        <a class="btn btn--ghost" href="/orders/<?= $orderId ?>">Refresh</a>
    </div>

    <div class="orderDetails__itemsList">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">🧾</div>
                <div class="empty-state__text">No items found for this order</div>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <?php
                $qty = (int) ($item['quantity'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $sub = $qty * $price;
                $productId = (int) ($item['productId'] ?? 0);
                $variantId = (int) ($item['variantId'] ?? 0);
                ?>
                <div class="orderDetails__itemRow">
                    <div class="orderDetails__itemMain">
                        <div class="orderDetails__itemName">Product #<?= htmlspecialchars((string) $productId, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="orderDetails__itemMeta">Variant #<?= htmlspecialchars((string) $variantId, ENT_QUOTES, 'UTF-8') ?> · Qty: <?= $qty ?> · Unit: €<?= htmlspecialchars(number_format($price, 2), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="orderDetails__itemSub">€<?= htmlspecialchars(number_format($sub, 2), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="orderDetails__itemsFooter">
        <span>Items total</span>
        <strong id="itemsTotal">€<?= htmlspecialchars(number_format($itemsTotal, 2), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
</div>