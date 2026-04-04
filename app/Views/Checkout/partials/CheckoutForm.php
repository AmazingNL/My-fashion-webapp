<form action="/checkout/place" method="POST" class="checkout__form" autocomplete="on">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="field">
        <label for="shippingAddress">Shipping address</label>
        <textarea id="shippingAddress" name="shippingAddress" placeholder="Street, City, Postcode, Country" required></textarea>
    </div>

    <div class="field">
        <label for="billingAddress">Billing address (optional)</label>
        <textarea id="billingAddress" name="billingAddress" placeholder="Leave empty to use shipping address"></textarea>
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
