<link rel="stylesheet" href="/assets/css/favourites.css">

<main class="fav">
    <header class="fav__head">
        <div>
            <h1 class="fav__title">My Favourites</h1>
            <p class="fav__sub">Your saved products, ready when you are.</p>
        </div>

        <div class="fav__actions">
            <a class="btn btn--ghost" href="/products">Back to products</a>
            <button id="clearFavBtn" class="btn btn--danger" type="button">Clear all</button>
        </div>
    </header>

    <section class="fav__notices">
        <div id="favError" class="notice notice--error" hidden></div>
        <div id="favSuccess" class="notice notice--success" hidden></div>
    </section>

    <section class="fav__grid" id="favGrid" aria-live="polite"></section>

    <section class="fav__empty" id="favEmpty" hidden>
        <h2>No favourites yet</h2>
        <p>Go to the products page and tap the heart to save items here.</p>
        <a class="btn" href="/products">Browse products</a>
    </section>
</main>

<script src="/assets/js/favourites.js" defer></script>