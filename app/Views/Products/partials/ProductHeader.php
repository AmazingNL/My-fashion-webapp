
<div class="product-header">
    <div class="category-wrapper">
        <span class="category-badge"><?= htmlspecialchars((string) $category, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <h1 class="product-title"><?= htmlspecialchars((string) $productName, ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="price-wrapper">
        <span class="currency">€</span>
        <span class="product-price"><?= htmlspecialchars((string) $price, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</div>

<?php if (!empty($description)): ?>
    <div class="description-container">
        <h3 class="section-title">About This Piece</h3>
        <div class="product-description"><?= nl2br(htmlspecialchars((string) $description, ENT_QUOTES, 'UTF-8')) ?></div>
    </div>
<?php endif; ?>