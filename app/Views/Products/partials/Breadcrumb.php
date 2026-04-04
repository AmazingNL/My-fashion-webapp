<?php
$breadcrumbItem = 'Collection';
$breadcrumbLink = '/productLists';
$breadcrumbActive = $productName ?? 'Product';
?>

<nav class="breadcrumb-nav">
    <div class="container-wide">
        <div class="breadcrumb">
            <svg class="breadcrumb-separator" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" />
            </svg>
            <a href="<?= htmlspecialchars((string) $breadcrumbLink, ENT_QUOTES, 'UTF-8') ?>" class="breadcrumb-item"><?= htmlspecialchars((string) $breadcrumbItem, ENT_QUOTES, 'UTF-8') ?></a>
            <svg class="breadcrumb-separator" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" />
            </svg>
            <span class="breadcrumb-item active"><?= htmlspecialchars((string) $breadcrumbActive, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
</nav>