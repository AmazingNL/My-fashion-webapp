<?php if (empty($products)): ?>
    <section class="fav__empty">
        <h2>No favourites yet</h2>
        <p>Go to the products page and tap the heart to save items here.</p>
        <a class="btn" href="/productLists">Browse products</a>
    </section>
<?php else: ?>
    <section class="fav__grid" aria-live="polite">
        <?php foreach ($products as $p): ?>
            <?php
            $id = (int) ($p['productId'] ?? 0);
            $name = (string) ($p['productName'] ?? 'Untitled');
            $category = (string) ($p['category'] ?? '');
            $price = (float) ($p['price'] ?? 0);
            $image = (string) ($p['image'] ?? '');
            $stock = isset($p['stock']) ? (string) $p['stock'] : '';
            ?>
            <article class="favCard">
                <div class="favCard__img">
                    <?php if ($image !== ''): ?>
                        <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                    <?php else: ?>
                        <div class="favCard__ph">No image</div>
                    <?php endif; ?>
                </div>

                <div class="favCard__body">
                    <div class="favCard__top">
                        <h3 class="favCard__title"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                        <span class="favCard__price">€<?= htmlspecialchars(number_format($price, 2), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <div class="favCard__meta">
                        <?php if ($category !== ''): ?>
                            <span class="pill"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <?php if ($stock !== ''): ?>
                            <span class="pill pill--soft">Stock: <?= htmlspecialchars($stock, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="favCard__actions">
                        <a class="btn btn--ghost" href="/products/<?= $id ?>">View</a>
                        <form method="post" action="/favourites/toggle" style="display:inline">
                            <?= $this->csrfField() ?>
                            <input type="hidden" name="productId" value="<?= $id ?>">
                            <input type="hidden" name="redirect" value="/favourites">
                            <button class="btn btn--danger" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>