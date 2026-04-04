<aside class="card checkout__summary">
    <h2 class="checkout__summaryTitle">Order summary</h2>

    <div class="checkout__summaryList">
        <?php if (!empty($cartItems)): ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="checkout__summaryItem">
                    <span class="checkout__itemName">
                        <?= htmlspecialchars($item['name'] ?? 'Product') ?>
                        <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                            <small class="checkout__variant">
                                <?= htmlspecialchars(trim(
                                    ($item['size'] ?? '') .
                                    (($item['size'] ?? '') && ($item['color'] ?? '') ? ' / ' : '') .
                                    ($item['color'] ?? '')
                                )) ?>
                            </small>
                        <?php endif; ?>
                    </span>
                    <span class="checkout__itemQty">x<?= (int) ($item['quantity'] ?? 1) ?></span>
                    <span class="checkout__itemPrice">
                        €<?= number_format((float) ($item['price'] ?? 0), 2, '.', '') ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="checkout__emptyCart">Your cart is empty</p>
        <?php endif; ?>
    </div>

    <div class="checkout__totalRow">
        <span>Total</span>
        <strong id="summaryTotal">
            €<?= number_format((float) ($total ?? 0), 2, '.', '') ?>
        </strong>
    </div>

    <a class="btn btn--ghost" href="/viewCart">Back to cart</a>
</aside>
