<?php
$productDetailsVm = $productDetailsVm ?? new \App\ViewModel\ProductDetailsVM();
$product = $productDetailsVm->getProduct();
$variants = $productDetailsVm->getVariants();

if ($product === null) {
    require __DIR__ . '/partials/ErrorState.php';
    return;
}

$productId = (int) ($product['productId'] ?? 0);
$productName = htmlspecialchars((string) ($product['productName'] ?? 'Product'), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars((string) ($product['description'] ?? ''), ENT_QUOTES, 'UTF-8');
$price = number_format((float) ($product['price'] ?? 0), 2);
$category = htmlspecialchars((string) ($product['category'] ?? ''), ENT_QUOTES, 'UTF-8');
$image = htmlspecialchars((string) ($product['image'] ?? '/assets/images/placeholder.jpg'), ENT_QUOTES, 'UTF-8');
?>

<main class="main-content">
    <?php require __DIR__ . '/partials/Breadcrumb.php'; ?>

    <section class="product-section">
        <div class="container-wide">
            <div class="product-grid">
                <?php require __DIR__ . '/partials/ProductGallery.php'; ?>

                <div class="product-info-wrapper">
                    <?php require __DIR__ . '/partials/ProductHeader.php'; ?>
                    <?php require __DIR__ . '/partials/ProductSelectionForm.php'; ?>
                </div>
            </div>
        </div>
    </section>
</main>