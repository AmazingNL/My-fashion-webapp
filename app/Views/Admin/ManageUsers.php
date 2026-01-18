<?php
/** @var array $users */
$users = $users ?? [];

function v($row, string $key, $default = null)
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
<meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="/assets/css/adminManageUsers.css">

<section class="admin-shell">
    <header class="admin-hero card">
        <div class="admin-hero__text">
            <h1>Manage Users</h1>
            <p class="muted">Activate/deactivate accounts and keep the kingdom tidy.</p>
        </div>

        <div class="admin-hero__stats">
            <div class="admin-stat">
                <span class="admin-stat__label">Total Users</span>
                <span class="admin-stat__value"><?= (int) count($users) ?></span>
            </div>
        </div>
    </header>

    <!-- Admin action navbar 
    <nav class="admin-nav card" aria-label="Admin actions">
        <button class="admin-nav__toggle" id="adminNavToggle" type="button" aria-label="Toggle admin navigation">
            ☰ Admin Menu
        </button>

        <div class="admin-nav__items" id="adminNavItems">
            <a class="admin-nav__link <?= $path === '/admin/dashboard' ? 'active' : '' ?>" href="/admin/dashboard">Dashboard</a>
            <a class="admin-nav__link <?= $path === '/admin/products' ? 'active' : '' ?>" href="/admin/products">Products</a>
            <a class="admin-nav__link <?= $path === '/admin/addProductForm' ? 'active' : '' ?>" href="/admin/addProductForm">Add Product</a>
            <a class="admin-nav__link <?= $path === '/admin/users' ? 'active' : '' ?>" href="/admin/users">Users</a>
            <a class="admin-nav__link" href="/admin/orders">Orders</a>
            <a class="admin-nav__link <?= $path === '/admin/appointments' ? 'active' : '' ?>" href="/admin/appointments">Appointments</a>
            <a class="admin-nav__link <?= $path === '/admin/activity' ? 'active' : '' ?>" href="/admin/activity">Activity Logs</a>
        </div>

        <div class="admin-nav__right">
            <label class="admin-search" for="userSearch">
                <span class="sr-only">Search users</span>
                <input id="userSearch" type="search" placeholder="Search name/email..." autocomplete="off">
            </label>
        </div>
    </nav>
-->
    <div class="notice notice--success" id="userOk" hidden></div>
    <div class="notice notice--error" id="userErr" hidden></div>

    <section class="admin-tools card">
        <div class="admin-tools__row">
            <div class="field admin-tools__field">
                <label for="userStatusFilter">Status</label>
                <select id="userStatusFilter">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="field admin-tools__field">
                <label for="userRoleFilter">Role</label>
                <select id="userRoleFilter">
                    <option value="all">All</option>
                    <option value="admin">Admin</option>
                    <option value="customer">Customer</option>
                </select>
            </div>
        </div>
    </section>

    <section class="card admin-tableWrap" aria-live="polite">
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">🪵</div>
                <p class="empty-state__text">No users found.</p>
            </div>
        <?php else: ?>
            <div class="admin-tableScroll">
                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th class="nowrap">Role</th>
                            <th class="nowrap">Status</th>
                            <th class="text-right nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php
                            $userId = (int) v($u, 'userId', v($u, 'id', 0));
                            $username = (string) v($u, 'username', v($u, 'name', 'User'));
                            $email = (string) v($u, 'email', '');
                            $roleValue = strtolower((string) v($u, 'role', 'customer'));
                            $isAdmin = ($roleValue === 'admin');
                            $isActive = (int) v($u, 'isActive', 0) === 1;
                            $role = $isAdmin ? 'admin' : 'customer';
                            $status = $isActive ? 'active' : 'inactive';
                            ?>
                            <tr data-user-id="<?= $userId ?>"
                                data-search="<?= htmlspecialchars(strtolower($username . ' ' . $email), ENT_QUOTES, 'UTF-8') ?>"
                                data-role="<?= $role ?>" data-status="<?= $status ?>">
                                <td>
                                    <div class="userCell">
                                        <span class="userAvatar"
                                            aria-hidden="true"><?= strtoupper(substr($username, 0, 1)) ?></span>
                                        <div class="userMeta">
                                            <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="muted">#<?= $userId ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="muted"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="pill <?= $isAdmin ? 'pill--primary' : '' ?>">
                                        <?= $isAdmin ? 'Admin' : 'Customer' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="statusPill <?= $isActive ? 'statusPill--ok' : 'statusPill--off' ?>">
                                        <?= $isActive ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button class="btn btn--ghost btn--sm jsToggleUser" type="button"
                                        data-next="<?= $isActive ? '0' : '1' ?>">
                                        <?= $isActive ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>

<script src="/assets/js/adminManageUsers.js"></script>