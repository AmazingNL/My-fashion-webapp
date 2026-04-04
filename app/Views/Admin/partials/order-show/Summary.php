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
