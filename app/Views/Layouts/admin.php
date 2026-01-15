<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel' ?></title>

    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/adminDashboard.css">
</head>
<body>

    <?php require __DIR__ . '/../Admin/partials/nav.php'; ?>

    <main class="admin-content">
        <?php require $content; ?>
    </main>

    <script src="/assets/js/adminManageProducts.js"></script>
    <script src="/assets/js/adminManageOrders.js"></script>
    <script src="/assets/js/adminManageUsers.js"></script>
</body>
</html>

<script src="/assets/js/admin.js"></script>
