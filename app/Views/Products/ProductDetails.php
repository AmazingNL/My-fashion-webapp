<link rel="stylesheet" href="/assets/css/productDetails.css">


<div class="container">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="/">Home</a>
        <span class="separator">›</span>
        <a href="/products">Products</a>
        <span class="separator">›</span>
        <span><?= htmlspecialchars($product->getName()); ?></span>
    </nav>

    <!-- Product Container -->
    <article class="product-container product" data-product-id="<?= (int) $product->getId(); ?>" data-variants='<?= htmlspecialchars(json_encode(array_map(fn($v) => [
           "variantId" => $v->getVariantId(),
           "size" => $v->getSize(),
           "color" => $v->getColor(),
           "stock" => $v->getStock()
       ], $variants)), ENT_QUOTES, "UTF-8"); ?>'>

        <!-- Product Gallery -->
        <div class="product-gallery">
            <div class="main-image">
                <img src="<?= htmlspecialchars($product->getImage() ?: '/images/placeholder.jpg'); ?>"
                    alt="<?= htmlspecialchars($product->getName()); ?>" class="product-img">

                <!-- Favorite Button -->
                <button id="favBtn" class="fav-btn" type="button" aria-label="Add to favourites" aria-pressed="false">
                    <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <!-- Product Header -->
            <div class="product-header">
                <h1 class="product-title"><?= htmlspecialchars($product->getName()); ?></h1>
                <span class="category-badge"><?= htmlspecialchars($product->getCategory()); ?></span>
            </div>

            <!-- Price Section -->
            <div class="price-section">
                <span class="price-label">Price:</span>
                <span class="product-price">€<?= number_format($product->getPrice(), 2); ?></span>
            </div>

            <!-- Description -->
            <p class="product-description"><?= htmlspecialchars($product->getDescription()); ?></p>

            <!-- Purchase Form -->
            <div id="buyForm" class="purchase-form">
                <?= $this->csrfField(); ?>

                <!-- Size and Color Selection -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="size">Size</label>
                        <select id="size" name="size" required class="form-select">
                            <option value="">Select size</option>
                            <?php foreach ($sizes as $size): ?>
                                <option value="<?= htmlspecialchars($size); ?>">
                                    <?= htmlspecialchars($size); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <select id="color" name="color" required class="form-select">
                            <option value="">Select color</option>
                            <?php foreach ($colors as $color): ?>
                                <option value="<?= htmlspecialchars($color); ?>">
                                    <?= htmlspecialchars(ucfirst($color)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Stock Information -->
                <div class="stock-info">
                    <div class="stock-indicator">
                        <svg class="stock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                        <span id="stockMsg" class="stock-message">Pick size + color to see stock.</span>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <div class="quantity-group">
                        <label for="qty">Quantity</label>
                        <input type="number" id="qty" name="qty" value="1" min="1" class="qty-input">
                    </div>

                    <button type="button" id="addBtn" class="btn btn-primary btn-large" disabled>
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        Add to Basket
                    </button>
                </div>
            </div>

            <!-- Back Button -->
            <div class="back-link">
                <a href="/products" class="btn btn-secondary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Back to Products
                </a>
            </div>
        </div>
    </article>

    <!-- Similar Products Section -->
    <?php if (!empty($similarProducts)): ?>
        <section class="similar-products">
            <div class="similar-products-header">
                <h2 class="similar-products-title">Similar Products</h2>
                <a href="/products?category=<?= urlencode($product->getCategory()); ?>" class="view-all-link">
                    View all
                    <svg class="view-all-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </a>
            </div>

            <div class="similar-products-grid">
                <?php foreach ($similarProducts as $item): ?>
                    <article class="product-card" data-product-id="<?= (int) $item->getId(); ?>">
                        <div class="product-card-image">
                            <a href="/products/<?= (int) $item->getId(); ?>">
                                <img src="<?= htmlspecialchars($item->getImage() ?: '/images/placeholder.jpg'); ?>"
                                    alt="<?= htmlspecialchars($item->getName()); ?>">
                            </a>

                            <!-- Favorite Button -->
                            <button class="product-card-fav" type="button" data-product-id="<?= (int) $item->getId(); ?>"
                                aria-label="Add to favourites">
                                <svg class="heart-icon-small" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                            </button>

                            <!-- Quick View Overlay -->
                            <div class="quick-view-overlay">
                                <a href="/products/<?= (int) $item->getId(); ?>" class="quick-view-btn">
                                    Quick View
                                </a>
                            </div>
                        </div>

                        <div class="product-card-content">
                            <p class="product-card-category"><?= htmlspecialchars($item->getCategory()); ?></p>
                            <h3 class="product-card-title">
                                <a href="/products/<?= (int) $item->getId(); ?>">
                                    <?= htmlspecialchars($item->getName()); ?>
                                </a>
                            </h3>
                            <p class="product-card-price">€<?= number_format($item->getPrice(), 2); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Toast Notification -->
    <div id="toast" class="toast" hidden></div>
</div>

<script src="/assets/js/productDetails.js"></script>