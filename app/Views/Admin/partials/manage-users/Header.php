<header class="admin-hero card">
    <div class="admin-hero__text">
        <h1>Manage Users</h1>
        <p class="muted">Delete customer accounts and keep users tidy.</p>
    </div>

    <div class="admin-hero__stats">
        <div class="admin-stat">
            <span class="admin-stat__label">Total Users</span>
            <span class="admin-stat__value"><?= (int) count($users) ?></span>
        </div>
    </div>
</header>
