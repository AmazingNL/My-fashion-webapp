<link rel="stylesheet" href="/assets/css/productDetails.css">

<!-- Decorative Header Pattern -->
<div class="header-pattern"></div>

<!-- Toast Notification -->
<div id="toast" class="toast" hidden></div>

<!-- Loading State -->
<div id="loadingState" class="loading-container">
    <div class="loading-spinner">
        <div class="spinner-ring"></div>
        <div class="spinner-ring"></div>
        <div class="spinner-ring"></div>
    </div>
    <p class="loading-text">Loading beautiful pieces...</p>
</div>

<!-- Error State -->
<div id="errorState" class="error-container" style="display: none;">
    <div class="error-content">
        <svg class="error-icon" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
            <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" />
            <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" />
        </svg>
        <h2 class="error-title">Product Not Found</h2>
        <p class="error-message">This piece seems to have found a new home already, or it never existed in our
            collection.</p>
        <a href="/products" class="btn btn--primary">Explore Our Collection</a>
    </div>
</div>

<!-- Main Content -->
<main id="mainContent" class="main-content" style="display: none;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav">
        <div class="container-wide">
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-item">Home</a>
                <svg class="breadcrumb-separator" viewBox="0 0 24 24">
                    <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
                <a href="/products" class="breadcrumb-item">Collection</a>
                <svg class="breadcrumb-separator" viewBox="0 0 24 24">
                    <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
                <span id="breadcrumbProduct" class="breadcrumb-item active">Product</span>
            </div>
        </div>
    </nav>

    <!-- Product Details Section -->
    <section class="product-section">
        <div class="container-wide">
            <div class="product-grid">
                <!-- Product Gallery -->
                <div class="product-gallery-wrapper">
                    <div class="gallery-main">
                        <div class="image-container">
                            <img id="productImage" src="/assets/images/placeholder.jpg" alt="Product"
                                class="product-image" />
                            <div class="image-overlay"></div>
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="product-info-wrapper">
                    <div class="product-header">
                        <div class="category-wrapper">
                            <span id="productCategory" class="category-badge">Category</span>
                        </div>
                        <h1 id="productName" class="product-title">Loading...</h1>
                        <div class="price-wrapper">
                            <span class="currency">€</span>
                            <span id="productPrice" class="product-price">0.00</span>
                        </div>
                    </div>

                    <div id="productDescriptionContainer" class="description-container" style="display: none;">
                        <h3 class="section-title">About This Piece</h3>
                        <div id="productDescription" class="product-description"></div>
                    </div>

                    <!-- Selection Form -->
                    <div class="selection-section">
                        <form id="addToBasketForm" class="product-form">
                            <!-- Variant Selection -->
                            <div class="form-group">
                                <label for="variantSelect" class="form-label">
                                    <span>Size & Color</span>
                                    <span class="label-required">*</span>
                                </label>
                                <div class="select-wrapper">
                                    <select id="variantSelect" name="variantId" class="form-select" required>
                                        <option value="">Choose your perfect fit...</option>
                                    </select>
                                    <svg class="select-arrow" viewBox="0 0 24 24">
                                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" fill="none" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Stock Indicator -->
                            <div id="stockInfo" class="stock-info" style="display:none;">
                                <svg class="stock-icon" viewBox="0 0 24 24" fill="none">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2"/>
                                    <polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <span class="stock-message">
                                    <strong id="stockCount" data-stock-for=""></strong>
                                    <span id="stockMessage"></span>
                                </span>
                            </div>

                            <!-- Quantity & Add to Cart -->
                            <div class="action-row">
                                <div class="quantity-selector">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <div class="quantity-input-wrapper">
                                        <button type="button" class="qty-btn qty-decrease" data-action="decrease">
                                            <svg viewBox="0 0 24 24">
                                                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor"
                                                    stroke-width="2" />
                                            </svg>
                                        </button>
                                        <input id="quantity" type="number" name="quantity" class="quantity-input"
                                            value="1" min="1" max="10" required />
                                        <button type="button" class="qty-btn qty-increase" data-action="increase">
                                            <svg viewBox="0 0 24 24">
                                                <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor"
                                                    stroke-width="2" />
                                                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor"
                                                    stroke-width="2" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <button id="addToBasket" type="button" class="btn btn-add-to-cart">
                                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none">
                                        <circle cx="9" cy="21" r="1" stroke="currentColor" stroke-width="2" />
                                        <circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2" />
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"
                                            stroke="currentColor" stroke-width="2" />
                                    </svg>
                                    <span class="btn-text">Add to Cart</span>
                                </button>
                            </div>

                            <input type="hidden" id="productIdInput" name="productId" value="">
                        </form>

                        <!-- Additional Info -->
                        <div class="product-features">
                            <div class="feature-item">
                                <svg class="feature-icon" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor"
                                        stroke-width="2" />
                                </svg>
                                <div class="feature-text">
                                    <strong>Authentic Designs</strong>
                                    <span>Handcrafted with care</span>
                                </div>
                            </div>
                            <div class="feature-item">
                                <svg class="feature-icon" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                    <polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="2" />
                                </svg>
                                <div class="feature-text">
                                    <strong>Fast Shipping</strong>
                                    <span>Delivered with love</span>
                                </div>
                            </div>
                            <div class="feature-item">
                                <svg class="feature-icon" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"
                                        stroke="currentColor" stroke-width="2" />
                                </svg>
                                <div class="feature-text">
                                    <strong>Need Help?</strong>
                                    <span>We're here for you</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Products -->
    <section id="similarProductsSection" class="similar-section" style="display: none;">
        <div class="container-wide">
            <div class="section-header">
                <h2 class="section-heading">You Might Also Love</h2>
                <a href="/products" class="view-all-link">
                    <span>View All</span>
                    <svg class="arrow-icon" viewBox="0 0 24 24">
                        <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" />
                        <polyline points="12 5 19 12 12 19" stroke="currentColor" stroke-width="2" />
                    </svg>
                </a>
            </div>

            <div id="similarProductsGrid" class="products-grid">
                <!-- JavaScript will populate similar products here -->
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/productDetails.js"></script>