<div class="cartItemBottom">
    <form class="cartEditForm" action="/updateQuantity" method="POST">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="productId" value="<?= $productId ?>">
        <input type="hidden" name="variantId" value="<?= $variantId ?>">

        <label class="cartEditLabel" for="qty-<?= $productId ?>-<?= $variantId ?>">Quantity</label>
        <div class="qtyBox">
            <input
                id="qty-<?= $productId ?>-<?= $variantId ?>"
                class="qtyInput"
                type="number"
                name="quantity"
                min="1"
                step="1"
                value="<?= $qty ?>"
                inputmode="numeric"
            >
            <button class="btn btnGhost" type="submit">Save</button>
        </div>
    </form>

    <form class="cartRemoveForm" action="/removeFromBasket" method="POST">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="productId" value="<?= $productId ?>">
        <input type="hidden" name="variantId" value="<?= $variantId ?>">
        <button class="iconBtn removeBtn" type="submit" title="Remove item" aria-label="Remove item">
            ✕
        </button>
    </form>

    <div class="cartPrices">
        <div class="unitPrice">€ <span><?= number_format($price, 2) ?></span></div>
        <div class="lineTotal">
            Line total: € <strong class="lineTotalValue"><?= number_format($lineTotal, 2) ?></strong>
        </div>
    </div>
</div>
