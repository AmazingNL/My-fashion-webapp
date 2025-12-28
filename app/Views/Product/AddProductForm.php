
<body>

    <main class="shell">

        <section class="panel" aria-labelledby="page-title">

            <div id="formErrors" class="notice notice--error" hidden></div>
            <div id="formSuccess" class="notice notice--success" hidden></div>
            <!-- Page header -->
            <header class="header">
                <div>
                    <span class="badge">Admin · Products</span>
                    <h1 id="page-title">Add new product</h1>
                    <p>Create a new product for your store catalog.</p>
                </div>
            </header>

            <!-- Product form -->
            <form method="post" action="/addProduct" class="form" id="addProductForm">
                <?= $this->csrfField(); ?>

                <fieldset class="form__grid">
                    <legend class="sr-only">Product details</legend>

                    <label class="field">
                        <input name="name" placeholder=" " required>
                        <span>Product name</span>
                    </label>

                    <label class="field">
                        <input name="category" placeholder=" " required>
                        <span>Category</span>
                    </label>

                    <label class="field">
                        <input name="price" type="number" step="0.01" placeholder=" " required>
                        <span>Price (€)</span>
                    </label>

                    <label class="field">
                        <input name="stock" type="number" placeholder=" " required>
                        <span>Stock</span>
                    </label>

                    <label class="field field--full">
                        <textarea name="description" placeholder=" " required></textarea>
                        <span>Description</span>
                    </label>

                    <label class="field field--full">
                        <input id="image" name="image" placeholder=" ">
                        <span>Image URL</span>
                    </label>

                    <!-- Image preview -->
                    <figure class="preview field--full">
                        <img id="previewImg" alt="" hidden>
                        <figcaption id="previewText">
                            Paste an image URL to preview
                        </figcaption>
                    </figure>

                </fieldset>

                <!-- Form actions -->
                <footer class="actions">
                    <button class="btn" type="submit">Save product</button>
                    <button class="btn btn--ghost" type="reset">Clear</button>
                </footer>

                <!-- Status message -->
                <output class="notice notice--success" hidden id="success">
                    Product ready to be saved.
                </output>

            </form>

        </section>

    </main>
    <script src="/assets/js/addProduct.js" defer></script>

</body>
