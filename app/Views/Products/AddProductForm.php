<link rel="stylesheet" href="/assets/css/addProductForm.css">


<body>
    <main class="shell">
        <section class="panel" aria-labelledby="page-title">
            <div class="topbar">
                <div>
                    <span class="badge">Admin · Products</span>
                    <h1 id="page-title">Add New Product</h1>
                    <p class="sub">Create a product and its variants in one go.</p>
                </div>
            </div>

            <div id="formErrors" class="notice notice--error" hidden></div>
            <div id="formSuccess" class="notice notice--success" hidden></div>

            <form method="post" action="/addProduct" class="form" id="addProductForm" enctype="multipart/form-data">
                <div class="grid">
                    <!-- Left: Product Details -->
                    <section class="card">
                        <header class="card__head">
                            <h2>Product Details</h2>
                            <p>Core information visible in your catalog.</p>
                        </header>

                        <div class="fields">
                            <label class="field">
                                <span>Product Name</span>
                                <input name="name" placeholder="Enter product name" required />
                            </label>

                            <label class="field">
                                <span>Category</span>
                                <input name="category" placeholder="e.g., Dresses, Tops, Accessories" required />
                            </label>

                            <div class="row2">
                                <label class="field">
                                    <span>Price (€)</span>
                                    <input name="price" type="number" step="0.01" placeholder="0.00" required />
                                </label>

                                <label class="field">
                                    <span>Stock</span>
                                    <input name="stock" type="number" min="0" placeholder="0" required />
                                </label>
                            </div>

                            <label class="field">
                                <span>Description</span>
                                <textarea name="description" placeholder="Describe your product in detail..."
                                    required></textarea>
                            </label>

                            <label class="field">
                                <span>Image URL</span>
                                <input type="file" id="image" name="image"
                                    accept="image/*" />
                            </label>

                            <figure class="preview">
                                <div class="preview__img">
                                    <img id="previewImg" alt="Product preview" hidden />
                                    <div class="preview__ph" id="previewPh">🖼️</div>
                                </div>
                                <figcaption class="preview__text" id="previewText">
                                    Paste an image URL to preview
                                </figcaption>
                            </figure>
                        </div>
                    </section>

                    <!-- Right: Variants -->
                    <section class="card">
                        <header class="card__head variantsHead">
                            <div>
                                <h2>Product Variants</h2>
                                <p>Add sizes, colors, and stock quantities.</p>
                            </div>

                            <button type="button" class="btn btn--ghost" id="addVariantBtn">
                                + Add Variant
                            </button>
                        </header>

                        <div id="variantsWrap" class="variantsWrap"></div>

                        <template id="variantTpl">
                            <div class="variantCard">
                                <div class="variantCard__top">
                                    <span class="pill">Variant</span>
                                    <button type="button" class="iconBtn removeVariantBtn" aria-label="Remove variant">
                                        ✕
                                    </button>
                                </div>

                                <div class="variantGrid">
                                    <label class="field">
                                        <span>Size</span>
                                        <input name="variants[0][size]" placeholder="e.g., S, M, L, XL" required />
                                    </label>

                                    <label class="field">
                                        <span>Color</span>
                                        <input name="variants[0][colour]" placeholder="e.g., Red, Blue" required />
                                    </label>

                                    <label class="field">
                                        <span>Variant Stock</span>
                                        <input name="variants[0][stockQuantity]" type="number" min="0" placeholder="0"
                                            required />
                                    </label>
                                </div>
                            </div>
                        </template>

                        <p class="hint">
                            💡 Tip: Overall product stock is separate from variant-specific stock quantities.
                        </p>
                    </section>
                </div>

                <footer class="footerActions">
                    <button class="btn btn--ghost" type="reset" id="resetBtn">Clear Form</button>
                    <button class="btn" type="submit">Save Product</button>
                </footer>
            </form>
        </section>
    </main>
    <script src="/assets/js/addProductForm.js" defer></script>
</body>