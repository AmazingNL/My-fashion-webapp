<?php
$product = $product ?? [];
$variants = $variants ?? [];

if (is_object($product)) {
    $product = (array) $product;
}

$productId = (int) ($product['productId'] ?? $product['id'] ?? 0);
$name = (string) ($product['productName'] ?? $product['name'] ?? '');
$desc = (string) ($product['description'] ?? '');
$price = (float) ($product['price'] ?? 0);
$category = (string) ($product['category'] ?? '');
$stock = (int) ($product['stock'] ?? $product['stockQuantity'] ?? 0);
$image = (string) ($product['image'] ?? '');

$normalizedVariants = [];
foreach ($variants as $variant) {
    if (is_object($variant)) {
        $variant = (array) $variant;
    }

    $normalizedVariants[] = [
        'variantId' => (int) ($variant['variantId'] ?? $variant['id'] ?? 0),
        'size' => (string) ($variant['size'] ?? ''),
        'colour' => (string) ($variant['colour'] ?? $variant['color'] ?? ''),
        'stockQuantity' => (int) ($variant['stockQuantity'] ?? $variant['stock'] ?? 0),
        'price' => (float) ($variant['price'] ?? 0),
    ];
}

$variants = $normalizedVariants;
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<section class="admin-shell">
    <?php require __DIR__ . '/partials/edit-product/Header.php'; ?>

    <form id="editProductForm" class="card form-card" method="POST" action="/admin/products/update" enctype="multipart/form-data">
        <?= $this->csrfField() ?>

        <?php require __DIR__ . '/partials/edit-product/ProductFields.php'; ?>
        <?php require __DIR__ . '/partials/edit-product/VariantsSection.php'; ?>
        <?php require __DIR__ . '/partials/edit-product/FormActions.php'; ?>
    </form>
</section>
