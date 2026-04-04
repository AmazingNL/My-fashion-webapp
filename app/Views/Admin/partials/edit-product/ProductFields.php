<input type="hidden" name="productId" value="<?= $productId ?>">

<?php if (!empty($_SESSION['csrf_token'])): ?>
    <input type="hidden" name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<div class="grid">
    <div class="field">
        <label for="productName">Product Name</label>
        <input id="productName" name="productName" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
            required>
    </div>

    <div class="field">
        <label for="category">Category</label>
        <input id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"
            required>
    </div>

    <div class="field">
        <label for="price">Base Price</label>
        <input id="price" name="price" type="number" step="0.01" min="0"
            value="<?= htmlspecialchars((string) $price, ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div class="field">
        <label for="stock">Base Stock</label>
        <input id="stock" name="stock" type="number" min="0"
            value="<?= htmlspecialchars((string) $stock, ENT_QUOTES, 'UTF-8') ?>" required>
        <small class="muted">If you track stock per variant, you can ignore base stock.</small>
    </div>
</div>

<div class="field">
    <label for="description">Description</label>
    <textarea id="description" name="description"
        rows="4"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></textarea>
</div>

<div class="image-row">
    <div class="image-preview">
        <?php if (!empty($image)): ?>
            <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                alt="Product image preview">
        <?php else: ?>
            <div class="image-ph">🧵 No image</div>
        <?php endif; ?>
    </div>

    <div class="field">
        <label for="image">Change Image</label>
        <input id="image" name="image" type="file" accept="image/*">
        <small class="muted">Leave empty to keep existing image.</small>
    </div>
</div>
