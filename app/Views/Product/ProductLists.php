<body>
    <main class="shell">
        <section class="panel store" aria-labelledby="storeTitle">
            <header class="store__top">
                <div>
                    <h1 id="storeTitle">Afro Catalogue</h1>
                    <p class="muted">Pick your vibe. Tap a product to see sizes + colours.</p>
                </div>

                <div class="store__actions">
                    <input id="q" class="search" type="search" placeholder="Search products…" autocomplete="off" />
                    <a class="btn btn--ghost" href="/cart">Basket <span id="cartCount" class="dot">0</span></a>
                </div>
            </header>

            <section id="grid" class="grid" aria-live="polite">
                <p class="muted">Loading products…</p>
            </section>
        </section>
    </main>
    <script defer src="/assets/js/productLists.js"></script>
</body>