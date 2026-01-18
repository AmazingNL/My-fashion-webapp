<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
?>

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
        <a class="admin-nav__link <?= $path === '/admin/orders' ? 'active' : '' ?>" href="/admin/orders">Orders</a>
        <a class="admin-nav__link <?= $path === '/admin/appointments' ? 'active' : '' ?>"
            href="/admin/appointments">Appointments</a>
        <a class="admin-nav__link <?= $path === '/admin/activity' ? 'active' : '' ?>" href="/admin/activity">Activity
            Logs</a>
    </div>

    <div class="admin-nav__right">
        <label class="admin-search" for="adminSearch">
            <span class="sr-only">Search admin actions</span>
            <input id="adminSearch" type="search" placeholder="Search actions..." autocomplete="off">
        </label>

        <!-- Logout -->
        <form action="/logout" method="POST" class="admin-logout-form">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">
            <button type="submit" class="admin-nav__link admin-nav__link--logout">
                <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                    <path
                        d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" />
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</nav>

<script>
    // Toggle mobile menu
    document.getElementById('adminNavToggle')?.addEventListener('click', function () {
        const items = document.getElementById('adminNavItems');
        items.classList.toggle('active');
        this.classList.toggle('active');
    });
</script>