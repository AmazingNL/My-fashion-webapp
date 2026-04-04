<aside class="cartSummary" aria-label="Cart summary">
    <div class="summaryCard">
        <h2>Summary</h2>

        <div class="summaryRow">
            <span>Items</span>
            <span><?= (int) ($itemCount ?? 0) ?></span>
        </div>

        <div class="summaryRow summaryTotal">
            <span>Total</span>
            <span>€ <strong><?= number_format((float) ($total ?? 0), 2) ?></strong></span>
        </div>

        <a href="/checkout" class="btn btnWide" <?= !empty($isEmpty) ? 'style="pointer-events:none;opacity:0.5"' : '' ?>>
            Checkout
        </a>
    </div>
</aside>
