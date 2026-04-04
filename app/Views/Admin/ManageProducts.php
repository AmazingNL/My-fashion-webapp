<?php
/** @var array $products */
$products = $products ?? [];
?>

<section class="admin-shell">
    <?php require __DIR__ . '/partials/manage-products/Header.php'; ?>

    <?php if (empty($products)): ?>
        <?php require __DIR__ . '/partials/manage-products/EmptyState.php'; ?>
    <?php else: ?>
        <?php require __DIR__ . '/partials/manage-products/ProductGrid.php'; ?>
    <?php endif; ?>
</section>
