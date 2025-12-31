<link rel="stylesheet" href="/assets/css/productLists.css">

<main class="shell">
        <section class="panel store" aria-labelledby="storeTitle">
            <div id="toast" class="toast" hidden></div>
            
            <header class="store__top">
                <div>
                    <h1 id="storeTitle">Afro Catalogue</h1>
                    <p class="muted">Pick your vibe. Tap a product to see sizes + colours.</p>
                </div>

                <div class="store__actions">
                    <input 
                        id="searchInput" 
                        class="search" 
                        type="search" 
                        placeholder="Search products..." 
                        autocomplete="off"
                        aria-label="Search products"
                    />
                    <a class="btn btn--ghost" href="/cart">
                        Basket <span id="cartCount" class="dot">0</span>
                    </a>
                </div>
            </header>

            <section id="grid" class="grid" aria-live="polite">
                <div class="loading">
                    <div class="loading__spinner"></div>
                    <p class="muted">Loading products...</p>
                </div>
            </section>
        </section>
    </main>
    <script src="/assets/js/productLists.js"></script>