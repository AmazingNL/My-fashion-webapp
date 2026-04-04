

<header class="cartHeader">
    <div>
        <h1 class="cartTitle"><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="cartSub"><?= $itemCount ?> item(s) in your basket</p>
    </div>

    <div class="cartHeaderActions">
        <a class="btn btnGhost" href="/productLists">Continue shopping</a>
        <form action="/clearBasket" method="POST">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn btnDanger" type="submit" <?= $isEmpty ? 'disabled' : '' ?>>
                Clear cart
            </button>
        </form>
    </div>
</header>