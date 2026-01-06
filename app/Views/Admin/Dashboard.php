<?php
/** @var array $stats */
$stats = $stats ?? [];

$totalProducts = (int)($stats['totalProducts'] ?? 0);
$totalUsers    = (int)($stats['totalUsers'] ?? 0);
$totalOrders   = (int)($stats['totalOrders'] ?? 0);
$pendingAppts  = (int)($stats['pendingAppointments'] ?? 0);

$recent = $stats['recentActivities'] ?? [];
?>

<link rel="stylesheet" href="/assets/css/adminDashboard.css">

<section class="admin-shell">
    <header class="admin-hero card">
        <div class="admin-hero__text">
            <h1>Admin Panel</h1>
            <p class="muted">
                Choose an action below. This page is your admin “remote control”.
            </p>
        </div>

        <div class="admin-hero__stats">
            <div class="admin-stat">
                <span class="admin-stat__label">Products</span>
                <span class="admin-stat__value"><?= $totalProducts ?></span>
            </div>
            <div class="admin-stat">
                <span class="admin-stat__label">Users</span>
                <span class="admin-stat__value"><?= $totalUsers ?></span>
            </div>
            <div class="admin-stat">
                <span class="admin-stat__label">Orders</span>
                <span class="admin-stat__value"><?= $totalOrders ?></span>
            </div>
            <div class="admin-stat">
                <span class="admin-stat__label">Pending Appts</span>
                <span class="admin-stat__value"><?= $pendingAppts ?></span>
            </div>
        </div>
    </header>

    <!-- Admin action navbar -->
    <nav class="admin-nav card" aria-label="Admin actions">
        <button class="admin-nav__toggle" id="adminNavToggle" type="button" aria-label="Toggle admin navigation">
            ☰ Admin Menu
        </button>

        <div class="admin-nav__items" id="adminNavItems">
            <a class="admin-nav__link" href="/admin/dashboard" data-admin-link>Dashboard</a>
            <a class="admin-nav__link" href="/admin/products" data-admin-link>Products</a>
            <a class="admin-nav__link" href="/admin/addProductForm" data-admin-link>Add Product</a>
            <a class="admin-nav__link" href="/admin/users" data-admin-link>Users</a>
            <a class="admin-nav__link" href="/admin/orders" data-admin-link>Orders</a>
            <a class="admin-nav__link" href="/admin/appointments" data-admin-link>Appointments</a>
            <a class="admin-nav__link" href="/admin/activity" data-admin-link>Activity Logs</a>
        </div>

        <div class="admin-nav__right">
            <label class="admin-search" for="adminSearch">
                <span class="sr-only">Search admin actions</span>
                <input id="adminSearch" type="search" placeholder="Search actions..." autocomplete="off">
            </label>
        </div>
    </nav>

    <!-- Action cards -->
    <section class="admin-grid" id="adminCards" aria-live="polite">
        <a class="admin-card card" href="/admin/products" data-title="products manage products catalogue">
            <div class="admin-card__icon">🧵</div>
            <div class="admin-card__body">
                <h3>Manage Products</h3>
                <p class="muted">Edit, delete, and maintain your catalogue.</p>
            </div>
            <span class="pill pill--primary">Open</span>
        </a>

        <a class="admin-card card" href="/admin/addProductForm" data-title="add product create new product">
            <div class="admin-card__icon">➕</div>
            <div class="admin-card__body">
                <h3>Add Product</h3>
                <p class="muted">Create products and variants with images.</p>
            </div>
            <span class="pill pill--primary">Open</span>
        </a>

        <a class="admin-card card" href="/admin/users" data-title="users manage users accounts">
            <div class="admin-card__icon">👤</div>
            <div class="admin-card__body">
                <h3>Manage Users</h3>
                <p class="muted">Activate/deactivate users and review accounts.</p>
            </div>
            <span class="pill pill--primary">Open</span>
        </a>

        <a class="admin-card card" href="/admin/orders" data-title="orders manage orders customer orders">
            <div class="admin-card__icon">🧾</div>
            <div class="admin-card__body">
                <h3>Orders</h3>
                <p class="muted">View and manage orders.</p>
            </div>
            <span class="pill pill--primary">Open</span>
        </a>

        <a class="admin-card card" href="/admin/appointments" data-title="appointments manage appointments slots">
            <div class="admin-card__icon">📅</div>
            <div class="admin-card__body">
                <h3>Appointments</h3>
                <p class="muted">Manage bookings, slots, and statuses.</p>
            </div>
            <span class="pill pill--primary">Open</span>
        </a>

        <a class="admin-card card" href="/admin/activity" data-title="activity logs audit trail">
            <div class="admin-card__icon">🧠</div>
            <div class="admin-card__body">
                <h3>Activity Logs</h3>
                <p class="muted">Audit trail: who did what, and when.</p>
            </div>
            <span class="pill pill--primary">Open</span>
        </a>
    </section>

    <!-- Recent activity -->
    <section class="admin-recent card">
        <header class="admin-recent__top">
            <h2>Recent Activity</h2>
            <a class="btn btn--ghost" href="/admin/activity">View all</a>
        </header>

        <?php if (empty($recent)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">🪵</div>
                <p class="empty-state__text">No recent activity yet.</p>
            </div>
        <?php else: ?>
            <ul class="admin-recent__list">
                <?php foreach ($recent as $row): ?>
                    <li class="admin-recent__item">
                        <span class="admin-recent__dot" aria-hidden="true"></span>
                        <div class="admin-recent__text">
                            <strong><?= htmlspecialchars((string)($row['action'] ?? 'Activity'), ENT_QUOTES, 'UTF-8') ?></strong>
                            <div class="muted">
                                <?= htmlspecialchars((string)($row['createdAt'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</section>

<script src="/assets/js/adminDashboard.js"></script>
