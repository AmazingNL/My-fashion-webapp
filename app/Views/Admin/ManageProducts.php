<?php
/** @var array $products */
$products = $products ?? [];
function money($n): string
{
    $num = (float) $n;
    return '€' . number_format($num, 2, '.', ',');
}


function pv($row, string $key, $default = null)
{
    if (is_array($row))
        return $row[$key] ?? $default;
    if (is_object($row)) {
        if (isset($row->$key))
            return $row->$key;
        $m = 'get' . ucfirst($key);
        if (method_exists($row, $m))
            return $row->$m();
    }
    return $default;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
?>
<link rel="stylesheet" href="/assets/css/adminManageProducts.css">

<section class="admin-shell">
    <header class="admin-hero card">
        <div class="admin-hero__text">
            <h1>Manage Products</h1>
            <p class="muted">Edit your catalogue, remove stale items, keep it fresh like Ankara on laundry day.</p>
        </div>

        <div class="admin-hero__cta">
            <a class="btn btn--primary" href="/admin/addProductForm">➕ Add Product</a>
        </div>
    </header>
<!-- -->

    <nav class="admin-nav card" aria-label="Admin actions">
        <button class="admin-nav__toggle" id="adminNavToggle" type="button" aria-label="Toggle admin navigation">
            ☰ Admin Menu
        </button>

        <div class="admin-nav__items" id="adminNavItems">
            <a class="admin-nav__link <?= $path === '/admin/dashboard' ? 'active' : '' ?>"
                href="/admin/dashboard">Dashboard</a>
            <a class="admin-nav__link <?= $path === '/admin/products' ? 'active' : '' ?>"
                href="/admin/products">Products</a>
            <a class="admin-nav__link <?= $path === '/admin/addProductForm' ? 'active' : '' ?>"
                href="/admin/addProductForm">Add Product</a>
            <a class="admin-nav__link <?= $path === '/admin/users' ? 'active' : '' ?>" href="/admin/users">Users</a>
            <a class="admin-nav__link" href="/admin/orders">Orders</a>
            <a class="admin-nav__link <?= $path === '/admin/appointments' ? 'active' : '' ?>"
                href="/admin/appointments">Appointments</a>
            <a class="admin-nav__link <?= $path === '/admin/activity' ? 'active' : '' ?>"
                href="/admin/activity">Activity Logs</a>
        </div>

        <div class="admin-nav__right">
            <label class="admin-search" for="productSearch">
                <span class="sr-only">Search products</span>
                <input id="productSearch" type="search" placeholder="Search name/category..." autocomplete="off">
            </label>
        </div>
    </nav>

    <div class="notice notice--success" id="prodOk" hidden></div>
    <div class="notice notice--error" id="prodErr" hidden></div>

    <section class="admin-tools card">
        <div class="admin-tools__row">
            <div class="field admin-tools__field">
                <label for="stockFilter">Stock</label>
                <select id="stockFilter">
                    <option value="all">All</option>
                    <option value="in">In stock</option>
                    <option value="low">Low stock (≤ 5)</option>
                    <option value="out">Out of stock</option>
                </select>
            </div>
        </div>
    </section>

    <?php if (empty($products)): ?>
        <section class="card">
            <div class="empty-state">
                <div class="empty-state__icon">🧵</div>
                <p class="empty-state__text">No products found.</p>
            </div>
        </section>
    <?php else: ?>
        <section class="admin-grid" id="productGrid" aria-live="polite">
            <?php foreach ($products as $p): ?>
                <?php
                $id = (int) pv($p, 'productId', pv($p, 'id', 0));
                $name = (string) pv($p, 'productName', pv($p, 'name', 'Product'));
                $cat = (string) pv($p, 'category', '—');
                $price = (float) pv($p, 'price', 0);
                $stock = (int) pv($p, 'stockQuantity', pv($p, 'stock', 0));
                $img = (string) pv($p, 'image', pv($p, 'imagePath', ''));
                $hay = strtolower($name . ' ' . $cat);
                $stockBand = $stock <= 0 ? 'out' : ($stock <= 5 ? 'low' : 'in');
                ?>
                <article class="admin-prod card" data-product-id="<?= $id ?>"
                    data-search="<?= htmlspecialchars($hay, ENT_QUOTES, 'UTF-8') ?>" data-stock-band="<?= $stockBand ?>">
                    <div class="admin-prod__media">
                        <?php if (!empty($img)): ?>
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <div class="admin-prod__ph" aria-hidden="true">🧵</div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-prod__body">
                        <header class="admin-prod__top">
                            <h3><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                            <span class="pill pill--primary">#<?= $id ?></span>
                        </header>

                        <div class="admin-prod__meta">
                            <span class="muted"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="muted">
                                <?= money($price) ?>
                            </span>
                        </div>

                        <div class="admin-prod__stock">
                            <span
                                class="statusPill <?= $stock <= 0 ? 'statusPill--off' : ($stock <= 5 ? 'statusPill--warn' : 'statusPill--ok') ?>">
                                Stock: <?= $stock ?>
                            </span>
                        </div>

                        <div class="admin-prod__actions">
                            <a class="btn btn--ghost btn--sm" href="/admin/products/edit/<?= $id ?>">Edit</a>
                            <button class="btn btn--danger btn--sm jsDeleteProduct" type="button">Delete</button>
                        </div>

                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</section>

<script src="/assets/js/adminManageProducts.js"></script>