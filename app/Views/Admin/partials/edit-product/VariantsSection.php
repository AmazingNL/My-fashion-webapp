<hr class="sep">

<div class="variants-head">
    <div>
        <h2>Variants</h2>
        <p class="muted">Edit existing variants and optionally add one new variant below.</p>
    </div>
</div>

<div class="variants-wrap">
    <?php foreach ($variants as $v): ?>
        <?php
        $variantId = (int) ($v['variantId'] ?? 0);
        $size = (string) ($v['size'] ?? '');
        $colour = (string) ($v['colour'] ?? '');
        $vStock = (int) ($v['stockQuantity'] ?? 0);
        $vPrice = (float) ($v['price'] ?? 0);
        ?>
        <div class="variantCard" data-existing="1">
            <input type="hidden" name="variantId[]" value="<?= $variantId ?>">

            <div class="variantGrid">
                <div class="field">
                    <label>Size</label>
                    <input name="variantSize[]" value="<?= htmlspecialchars($size, ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="field">
                    <label>Colour</label>
                    <input name="variantColour[]" value="<?= htmlspecialchars($colour, ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="field">
                    <label>Stock</label>
                    <input name="variantStock[]" type="number" min="0"
                        value="<?= htmlspecialchars((string) $vStock, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="field">
                    <label>Price</label>
                    <input name="variantPrice[]" type="number" step="0.01" min="0"
                        value="<?= htmlspecialchars((string) $vPrice, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="variantActions">
                <span class="muted">Variant #<?= $variantId ?></span>
                <label class="muted" style="display:flex; align-items:center; gap:.5rem;">
                    <span>Delete</span>
                    <input type="checkbox" name="variantDeleteIds[]" value="<?= $variantId ?>">
                </label>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="variantCard" data-existing="0">
        <input type="hidden" name="variantId[]" value="0">

        <div class="variantGrid">
            <div class="field">
                <label>Size</label>
                <input name="variantSize[]" placeholder="Optional new size">
            </div>

            <div class="field">
                <label>Colour</label>
                <input name="variantColour[]" placeholder="Optional new colour">
            </div>

            <div class="field">
                <label>Stock</label>
                <input name="variantStock[]" type="number" min="0" value="0">
            </div>

            <div class="field">
                <label>Price</label>
                <input name="variantPrice[]" type="number" step="0.01" min="0" value="0">
            </div>
        </div>

        <div class="variantActions">
            <span class="muted">New variant (optional)</span>
        </div>
    </div>
</div>
