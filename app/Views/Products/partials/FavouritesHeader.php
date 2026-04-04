<header class="fav__head">
    <div>
        <h1 class="fav__title">My Favourites</h1>
        <p class="fav__sub">Your saved products, ready when you are.</p>
    </div>

    <div class="fav__actions">
        <a class="btn btn--ghost" href="/productLists">Back to products</a>
        <form method="post" action="/favourites/clear" style="display:inline">
            <?= $this->csrfField() ?>
            <button class="btn btn--danger" type="submit">Clear all</button>
        </form>
    </div>
</header>