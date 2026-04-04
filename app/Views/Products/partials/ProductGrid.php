 <?php
$products = $products ?? [];
?>

<?php if (empty($products)): ?>
    <div class="empty-state"><div class="empty-state__icon">🔍</div><p class="empty-state__text">No products found</p></div>
<?php else: ?>
    <?php foreach ($products as $p): ?>
        <?php
        $id = (int)($p['productId'] ?? 0);
        $favourites = $_SESSION['favourites'] ?? [];
        $isFavourited = isset($favourites[$id]);
        $name = htmlspecialchars($p['productName'] ?? 'Product', ENT_QUOTES, 'UTF-8');
        $price = number_format((float)($p['price'] ?? 0), 2);
        $category = htmlspecialchars($p['category'] ?? '', ENT_QUOTES, 'UTF-8');
        $image = htmlspecialchars($p['image'] ?? '/assets/images/placeholder.jpg', ENT_QUOTES, 'UTF-8');
        $redirectTo = htmlspecialchars((string) ($_SERVER['REQUEST_URI'] ?? '/productLists'), ENT_QUOTES, 'UTF-8');
        ?>
        <article class="card">
            <form method="post" action="/favourites/toggle" style="display:inline">
                <?= $this->csrfField() ?>
                <input type="hidden" name="productId" value="<?= $id ?>">
                <input type="hidden" name="redirect" value="<?= $redirectTo ?>">
                <button class="card__fav" type="submit" aria-pressed="<?= $isFavourited ? 'true' : 'false' ?>" title="<?= $isFavourited ? 'Remove from favourites' : 'Add to favourites' ?>">♥</button>
            </form>
            <a class="card__link" href="/products/<?= $id ?>">
                <img class="card__img" src="<?= $image ?>" alt="<?= $name ?>">
                <h2 class="card__title"><?= $name ?></h2>
                <div class="card__meta">
                    <span class="card__price">€<?= $price ?></span>
                    <span class="card__category"><?= $category ?></span>
                </div>
            </a>
        </article>
    <?php endforeach; ?>
<?php endif; ?>