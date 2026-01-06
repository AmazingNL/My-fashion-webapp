<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
?>
<link rel="stylesheet" href="/assets/css/adminManageOrders.css">

<section class="admin-shell">
    <header class="admin-hero card">
        <div class="admin-hero__text">
            <h1>Manage Orders</h1>
            <p class="muted">Loaded from your JSON API. Fast, clean, no page refresh acrobatics.</p>
        </div>
    </header>

    <nav class="admin-nav card" aria-label="Admin actions">
        <button class="admin-nav__toggle" id="adminNavToggle" type="button" aria-label="Toggle admin navigation">
            ☰ Admin Menu
        </button>

        <div class="admin-nav__items" id="adminNavItems">
            <a class="admin-nav__link <?= $path === '/admin/dashboard' ? 'active' : '' ?>" href="/admin/dashboard">Dashboard</a>
            <a class="admin-nav__link <?= $path === '/admin/products' ? 'active' : '' ?>" href="/admin/products">Products</a>
            <a class="admin-nav__link <?= $path === '/admin/addProductForm' ? 'active' : '' ?>" href="/admin/addProductForm">Add Product</a>
            <a class="admin-nav__link <?= $path === '/admin/users' ? 'active' : '' ?>" href="/admin/users">Users</a>
            <a class="admin-nav__link active" href="/admin/orders">Orders</a>
            <a class="admin-nav__link <?= $path === '/admin/appointments' ? 'active' : '' ?>" href="/admin/appointments">Appointments</a>
            <a class="admin-nav__link <?= $path === '/admin/activity' ? 'active' : '' ?>" href="/admin/activity">Activity Logs</a>
        </div>

        <div class="admin-nav__right">
            <label class="admin-search" for="orderSearch">
                <span class="sr-only">Search orders</span>
                <input id="orderSearch" type="search" placeholder="Search order id/status..." autocomplete="off">
            </label>
        </div>
    </nav>

    <div class="notice notice--success" id="ordOk" hidden></div>
    <div class="notice notice--error" id="ordErr" hidden></div>

    <section class="admin-tools card">
        <div class="admin-tools__row">
            <div class="field admin-tools__field">
                <label for="statusFilter">Status</label>
                <select id="statusFilter">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>
    </section>

    <section class="card admin-tableWrap">
        <div class="loading" id="ordersLoading">
            <div class="loading__spinner"></div>
            <p class="muted">Loading orders…</p>
        </div>

        <div class="admin-tableScroll" id="ordersTableWrap" hidden>
            <table class="admin-table" id="ordersTable">
                <thead>
                    <tr>
                        <th class="nowrap">Order</th>
                        <th>Status</th>
                        <th class="nowrap">Total</th>
                        <th class="nowrap">Created</th>
                        <th class="text-right nowrap">Action</th>
                    </tr>
                </thead>
                <tbody id="ordersTbody"></tbody>
            </table>
        </div>

        <div class="empty-state" id="ordersEmpty" hidden>
            <div class="empty-state__icon">🧾</div>
            <p class="empty-state__text">No orders found.</p>
        </div>
    </section>
</section>

<script src="/assets/js/adminManageOrders.js"></script>
