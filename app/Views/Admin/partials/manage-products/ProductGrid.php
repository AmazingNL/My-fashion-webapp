<section class="admin-grid" id="productGrid" aria-live="polite">
    <?php foreach ($products as $p): ?>
        <?php
        $id = (int) ($p['productId'] ?? $p['id'] ?? 0);
        $name = (string) ($p['productName'] ?? $p['name'] ?? 'Product');
        $cat = (string) ($p['category'] ?? '-');
        $price = (float) ($p['price'] ?? 0);
        $stock = (int) ($p['stockQuantity'] ?? $p['stock'] ?? 0);
        $img = (string) ($p['image'] ?? $p['imagePath'] ?? '');
        ?>
        <article class="admin-prod card" data-product-id="<?= $id ?>">
            <div class="admin-prod__media">
                <?php if ($img !== ''): ?>
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <div class="admin-prod__ph" aria-hidden="true">No image</div>
                <?php endif; ?>
            </div>

            <div class="admin-prod__body">
                <header class="admin-prod__top">
                    <h3><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                    <span class="pill pill--primary">#<?= $id ?></span>
                </header>

                <div class="admin-prod__meta">
                    <span class="muted"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="muted">EUR <?= htmlspecialchars(number_format($price, 2, '.', ','), ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="admin-prod__stock">
                    <span class="statusPill <?= $stock <= 0 ? 'statusPill--off' : ($stock <= 5 ? 'statusPill--warn' : 'statusPill--ok') ?>">
                        Stock: <?= $stock ?>
                    </span>
                </div>

                <div class="admin-prod__actions">
                    <a class="btn btn--ghost btn--sm" href="/admin/products/edit/<?= $id ?>">Edit</a>
                    <form method="post" action="/admin/products/delete" style="display:inline; margin:0;">
                        <?= $this->csrfField() ?>
                        <input type="hidden" name="productId" value="<?= $id ?>">
                        <button class="btn btn--danger btn--sm" type="submit" onclick="return confirm('Delete this product?')">Delete</button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>
