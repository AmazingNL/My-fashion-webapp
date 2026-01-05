<?php
$items = array_map(function ($it) {
    $name = (string) ($it['name'] ?? 'Product');
    $qty = (int) ($it['quantity'] ?? 0);
    $price = (float) ($it['price'] ?? 0);

    $size = trim((string) ($it['size'] ?? ''));
    $color = trim((string) ($it['color'] ?? ''));
    $variantLabel = trim($size . ($size && $color ? ' / ' : '') . $color);

    return [
        'name' => $name,
        'qty' => $qty,
        'price' => $price,
        'variantLabel' => $variantLabel,
    ];
}, $cartItems ?? []);
?>
<script id="checkoutData" type="application/json">
<?= json_encode([
    'items' => $items,
    'total' => (float)($total ?? 0),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>

<link rel="stylesheet" href="/assets/css/checkout.css">

<section class="checkout">
    <div class="checkout__head">
        <h1>Checkout</h1>
        <p class="checkout__sub">Confirm your details and place your order ✨</p>
    </div>

    <div class="checkout__layout">
        <!-- Form -->
        <div class="card checkout__formCard">
            <div id="checkoutError" class="notice notice--error" hidden></div>
            <div id="checkoutSuccess" class="notice notice--success" hidden></div>

            <form id="checkoutForm" class="checkout__form" autocomplete="on">
                <div class="field">
                    <label for="shippingAddress">Shipping address</label>
                    <textarea id="shippingAddress" name="shippingAddress" placeholder="Street, City, Postcode, Country"
                        required></textarea>
                </div>

                <div class="field">
                    <label for="billingAddress">Billing address (optional)</label>
                    <textarea id="billingAddress" name="billingAddress"
                        placeholder="Leave empty to use shipping address"></textarea>
                </div>

                <div class="field">
                    <label for="paymentMethod">Payment method</label>
                    <select id="paymentMethod" name="paymentMethod">
                        <option value="credit_card">Credit card</option>
                        <option value="ideal">iDEAL</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank transfer</option>
                    </select>
                </div>

                <button class="btn btn--primary checkout__btn" type="submit">
                    Place order
                    <span aria-hidden="true">→</span>
                </button>

            </form>
        </div>

        <!-- Summary -->
        <aside class="card checkout__summary">
            <h2 class="checkout__summaryTitle">Order summary</h2>

            <div id="summaryList" class="checkout__summaryList" aria-live="polite"></div>

            <div class="checkout__totalRow">
                <span>Total</span>
                <strong id="summaryTotal">
                    <?= number_format((float) ($total ?? 0), 2, '.', '') ?>
                </strong>
            </div>

            <a class="btn btn--ghost" href="/viewCart">Back to cart</a>
        </aside>
    </div>
</section>




<script src="/assets/js/checkout.js?v=<?= time() ?>"></script>


