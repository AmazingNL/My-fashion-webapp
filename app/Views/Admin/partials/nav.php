<nav class="admin-nav card" aria-label="Admin actions">
    <button class="admin-nav__toggle" id="adminNavToggle" type="button" aria-label="Toggle admin navigation">
        ☰ Admin Menu
    </button>

    <div class="admin-nav__items" id="adminNavItems">
        <a class="admin-nav__link" href="/admin/dashboard">Dashboard</a>
        <a class="admin-nav__link" href="/admin/products">Products</a>
        <a class="admin-nav__link" href="/admin/addProductForm">Add Product</a>
        <a class="admin-nav__link" href="/admin/users">Users</a>
        <a class="admin-nav__link" href="/admin/orders">Orders</a>
        <a class="admin-nav__link" href="/admin/appointments">Appointments</a>
        <a class="admin-nav__link" href="/admin/activity">Activity Logs</a>
    </div>

    <div class="admin-nav__right">
        <label class="admin-search" for="adminSearch">
            <span class="sr-only">Search admin actions</span>
            <input id="adminSearch" type="search" placeholder="Search actions..." autocomplete="off">
        </label>

        <!-- Logout -->
        <form action="/logout" method="POST" class="admin-logout-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <button type="submit" class="admin-logout-btn">Logout</button>
        </form>
    </div>
</nav>
