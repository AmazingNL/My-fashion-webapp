
<section class="checkout">
    <div class="checkout__head">
        <h1>Checkout</h1>
        <p class="checkout__sub">Confirm your details and place your order ✨</p>
    </div>

    <div class="checkout__layout">
        <!-- Form -->
        <div class="card checkout__formCard">
            <?php if (!empty($noticeMessage)): ?>
                <div class="notice notice--<?= htmlspecialchars($noticeType === 'error' ? 'error' : 'success') ?>">
                    <?= htmlspecialchars($noticeMessage) ?>
                </div>
            <?php endif; ?>

            <?php require __DIR__ . '/partials/CheckoutForm.php'; ?>
        </div>

        <!-- Summary -->
        <?php require __DIR__ . '/partials/CheckoutSummary.php'; ?>
    </div>
</section>



