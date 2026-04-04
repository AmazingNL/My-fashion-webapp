<aside class="orderSummaryCard">
    <h2 class="orderSummaryTitle">Summary</h2>

    <div class="orderSummaryBox">
        <div class="sRow">
            <span>Items total</span>
            <strong>€<?= htmlspecialchars(number_format($itemsTotal, 2)) ?></strong>
        </div>
        <div class="sRow muted">
            <span>Shipping</span>
            <span>€0.00</span>
        </div>
        <div class="sRow muted">
            <span>Tax</span>
            <span>€0.00</span>
        </div>

        <div class="sDivider"></div>

        <div class="sRow total">
            <span>Total</span>
            <strong>€<?= htmlspecialchars(number_format((float) ($order->totalAmount ?? 0), 2)) ?></strong>
        </div>
    </div>

    <a class="orderSummaryBtn" href="/admin/orders/<?= $id ?>">Back to Order</a>
</aside>
