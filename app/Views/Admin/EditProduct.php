<?php
/**
 * Expected:
 * $product  (array/object)
 * $variants (array of array/object)  optional
 */

function val($row, string $key, $default = null)
{
    if (is_array($row))
        return $row[$key] ?? $default;
    if (is_object($row)) {
        if (isset($row->$key))
            return $row->$key;
        $m = 'get' . ucfirst($key);
        if (method_exists($row, $m))
            return $row->$m();
    }
    return $default;
}

$product = $product ?? [];
$variants = $variants ?? [];

$productId = (int) val($product, 'productId', val($product, 'id', 0));
$name = (string) val($product, 'productName', val($product, 'name', ''));
$desc = (string) val($product, 'description', '');
$price = (float) val($product, 'price', 0);
$category = (string) val($product, 'category', '');
$stock = (int) val($product, 'stock', val($product, 'stockQuantity', 0));
$image = (string) val($product, 'image', '');
?>
<link rel="stylesheet" href="/assets/css/adminEditProduct.css">

<section class="admin-shell">
    <header class="admin-hero card">
        <div>
            <h1>Edit Product</h1>
            <p class="muted">Update product details and manage variants.</p>
        </div>
        <div class="admin-hero__actions">
            <a class="btn btn--ghost" href="/admin/products">← Back to Products</a>
            <button class="btn btn--primary" form="editProductForm" type="submit">Save Changes</button>
        </div>
    </header>

    <div class="notice notice--success" id="formOk" hidden></div>
    <div class="notice notice--error" id="formErr" hidden></div>

    <form id="editProductForm" class="card form-card" method="POST" action="/admin/products/update"
        enctype="multipart/form-data">
        <?= $this->csrfField() ?>

        <input type="hidden" name="productId" value="<?= $productId ?>">

        <!-- if you use CSRF in your project, keep this -->
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
                    <img id="previewImg" src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Product image preview">
                <?php else: ?>
                    <div class="image-ph" id="previewPh">🧵 No image</div>
                    <img id="previewImg" alt="Product image preview" hidden>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="image">Change Image</label>
                <input id="image" name="image" type="file" accept="image/*">
                <small class="muted">Leave empty to keep existing image.</small>
            </div>
        </div>

        <hr class="sep">

        <div class="variants-head">
            <div>
                <h2>Variants</h2>
                <p class="muted">Add/edit sizes & colours. New rows will be created as new variants.</p>
            </div>
            <button class="btn btn--secondary" id="addVariantBtn" type="button">+ Add Variant</button>
        </div>

        <div class="variants-wrap" id="variantsWrap">
            <?php foreach ($variants as $v): ?>
                <?php
                $variantId = (int) val($v, 'variantId', val($v, 'id', 0));
                $size = (string) val($v, 'size', '');
                $colour = (string) val($v, 'colour', val($v, 'color', ''));
                $vStock = (int) val($v, 'stockQuantity', val($v, 'stock', 0));
                $vPrice = (float) val($v, 'price', 0);
                ?>
                <div class="variantCard" data-existing="1">
                    <input type="hidden" name="variantId[]" value="<?= $variantId ?>">
                    <input type="hidden" name="variantDelete[]" value="0" class="variantDelete">

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
                        <button class="btn btn--danger btn--sm removeVariantBtn" type="button">Remove</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <template id="variantTpl">
            <div class="variantCard" data-existing="0">
                <input type="hidden" name="variantId[]" value="0">
                <input type="hidden" name="variantDelete[]" value="0" class="variantDelete">

                <div class="variantGrid">
                    <div class="field">
                        <label>Size</label>
                        <input name="variantSize[]" required>
                    </div>

                    <div class="field">
                        <label>Colour</label>
                        <input name="variantColour[]" required>
                    </div>

                    <div class="field">
                        <label>Stock</label>
                        <input name="variantStock[]" type="number" min="0" value="0" required>
                    </div>

                    <div class="field">
                        <label>Price</label>
                        <input name="variantPrice[]" type="number" step="0.01" min="0" value="0" required>
                    </div>
                </div>

                <div class="variantActions">
                    <span class="muted">New variant</span>
                    <button class="btn btn--danger btn--sm removeVariantBtn" type="button">Remove</button>
                </div>
            </div>
        </template>

        <div class="form-actions">
            <a class="btn btn--ghost" href="/admin/products">Cancel</a>
            <button class="btn btn--primary" type="submit">Save Changes</button>
        </div>
    </form>
</section>

<script src="/assets/js/adminEditProduct.js"></script>