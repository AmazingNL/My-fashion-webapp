<link rel="stylesheet" href="/assets/css/main.css">

<main class="shell">
    <section class="panel">
        <div id="toast" class="toast" hidden></div>
        
        <header class="store__top">
            <div>
                <h1>Manage Products</h1>
                <p class="muted">Add, edit, or remove products from your catalogue.</p>
            </div>

            <div class="store__actions">
                <a class="btn btn--primary" href="/admin/products/create">
                    + Add New Product
                </a>
            </div>
        </header>

        <section id="productsTable" class="grid" aria-live="polite">
            <div class="loading">
                <div class="loading__spinner"></div>
                <p class="muted">Loading products...</p>
            </div>
        </section>
    </section>
</main>

<script src="/assets/js/manageProducts.js"></script>