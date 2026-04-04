<main class="shell">
    <section class="panel store">
        <?php
        $productListVm = $productListVm ?? new \App\ViewModel\ProductListVM();
        $filterCategories = $filterCategories ?? [];
        $currentFilters = $currentFilters ?? [];
        $products = $productListVm->getProducts();
        ?>

        <div id="toast" class="toast" hidden></div>

        <!-- HEADER -->
        <header class="store__top">
            <h1>Afro Catalogue</h1>
            <p class="muted">Explore authentic African fashion curated with pride.</p>
        </header>

        <!-- LAYOUT -->
        <div class="store__layout">

            <?php require __DIR__ . '/partials/FilterPanel.php'; ?>

            <!-- PRODUCT GRID -->
            <section id="productGrid" class="grid">
                <?php require __DIR__ . '/partials/ProductGrid.php'; ?>
            </section>

            <?php require __DIR__ . '/partials/Pagination.php'; ?>


        </div>
    </section>
</main>