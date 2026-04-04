<?php
$csrf = $csrf ?? '';
?>

<div class="selection-section">
    <form id="addToBasketForm" class="product-form" action="/addToBasket" method="POST">
        <div class="form-group">
            <label for="variantSelect" class="form-label">
                <span>Size & Color</span>
                <span class="label-required">*</span>
            </label>
            <div class="select-wrapper">
                <select id="variantSelect" name="variantId" class="form-select" required>
                    <option value="">Choose your perfect fit...</option>
                    <?php foreach ($variants as $v): ?>
                        <?php
                        $variantId = (int) ($v['variantId'] ?? 0);
                        $size = htmlspecialchars((string) ($v['size'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $colour = htmlspecialchars((string) ($v['colour'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $stock = (int) ($v['stockQuantity'] ?? 0);
                        $disabled = $stock <= 0 ? 'disabled' : '';
                        $label = $size . ' - ' . $colour . ' (' . $stock . ' in stock)';
                        if ($stock <= 0) {
                            $label .= ' (Out of Stock)';
                        }
                        ?>
                        <option value="<?= $variantId ?>" <?= $disabled ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <svg class="select-arrow" viewBox="0 0 24 24">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
            </div>
        </div>

        <div class="action-row">
            <div class="quantity-selector">
                <label for="quantity" class="form-label">Quantity</label>
                <div class="quantity-input-wrapper">
                    <button type="button" class="qty-btn qty-decrease" data-action="decrease" aria-label="Decrease quantity">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </button>
                    <input id="quantity" type="number" name="quantity" class="quantity-input" value="1" min="1" max="10" required />
                    <button type="button" class="qty-btn qty-increase" data-action="increase" aria-label="Increase quantity">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" />
                            <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </button>
                </div>
            </div>

            <button id="addToBasket" type="submit" class="btn btn-add-to-cart">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none">
                    <circle cx="9" cy="21" r="1" stroke="currentColor" stroke-width="2" />
                    <circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke="currentColor" stroke-width="2" />
                </svg>
                <span class="btn-text">Add to Cart</span>
            </button>
        </div>

        <input type="hidden" name="csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="productId" value="<?= $productId ?>">
    </form>

    <script>
        (function () {
            const wrapper = document.querySelector('#addToBasketForm .quantity-input-wrapper');
            if (!wrapper || wrapper.dataset.qtyBound === '1') return;

            const input = wrapper.querySelector('.quantity-input');
            const decreaseBtn = wrapper.querySelector('.qty-btn[data-action="decrease"]');
            const increaseBtn = wrapper.querySelector('.qty-btn[data-action="increase"]');
            if (!input) return;

            const clamp = () => {
                const min = Number(input.min || 1);
                const max = Number(input.max || 999);
                const value = Number(input.value || min);
                input.value = String(Math.max(min, Math.min(max, value)));
            };

            decreaseBtn && decreaseBtn.addEventListener('click', function (event) {
                event.preventDefault();
                input.value = String(Number(input.value || input.min || 1) - 1);
                clamp();
            });

            increaseBtn && increaseBtn.addEventListener('click', function (event) {
                event.preventDefault();
                input.value = String(Number(input.value || input.min || 1) + 1);
                clamp();
            });

            input.addEventListener('input', clamp);
            wrapper.dataset.qtyBound = '1';
        })();
    </script>

    <?php require __DIR__ . '/ProductFeatures.php'; ?>
</div>