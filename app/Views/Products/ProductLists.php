<link rel="stylesheet" href="/assets/css/productLists.css">

<main class="shell">
    <section class="panel store">

        <div id="toast" class="toast" hidden></div>

        <!-- HEADER -->
        <header class="store__top">
            <h1>Afro Catalogue</h1>
            <p class="muted">Explore authentic African fashion curated with pride.</p>
        </header>

        <!-- LAYOUT -->
        <div class="store__layout">

            <!-- FILTER PANEL -->
            <aside id="filterPanel" class="filters">
                <div class="filters__header">
                    <h2 class="filters__title">Filter</h2>
                    <button id="filterClose" class="btn btn--ghost filters__close" type="button">Close</button>
                </div>

                <div class="filters__section">
                    <h3 class="filters__heading">Category</h3>
                    <div id="categoryFilters" class="filters__list"></div>
                </div>

                <div class="filters__section">
                    <h3 class="filters__heading">Price</h3>

                    <div id="priceRangeFilters" class="filters__list"></div>

                    <div class="filters__row">
                        <div class="field">
                            <label>Min (€)</label>
                            <input id="minPrice" type="number" min="0" placeholder="0">
                        </div>
                        <div class="field">
                            <label>Max (€)</label>
                            <input id="maxPrice" type="number" min="0" placeholder="500">
                        </div>
                    </div>
                </div>

                <div class="filters__actions">
                    <button id="clearFilters" class="btn btn--secondary" type="button">
                        Clear Filters
                    </button>
                </div>
            </aside>

            <!-- PRODUCT GRID -->
            <section id="grid" class="grid">
                <div class="loading">
                    <div class="loading__spinner"></div>
                    <p class="muted">Loading products…</p>
                </div>
            </section>


        </div>
    </section>
</main>

<script src="/assets/js/productLists.js"></script>