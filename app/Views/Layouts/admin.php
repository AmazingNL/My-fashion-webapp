<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Admin Panel' ?></title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

    <?php require __DIR__ . '/../Admin/Dashboard.php'; ?>

    <main class="admin-content">
        <?= $content ?>
    </main>

    <script src="/assets/js/admin.js"></script>
</body>
</html>
