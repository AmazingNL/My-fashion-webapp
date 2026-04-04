<header class="admin-hero card">
    <div class="admin-hero__text">
        <h1>Admin Panel</h1>
        <p class="muted">
            Choose an action below. This page is your admin "remote control".
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
