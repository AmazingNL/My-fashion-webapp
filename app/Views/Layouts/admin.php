<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <title><?= $title ?? 'Admin Panel' ?></title>

    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/adminDashboard.css">
    <link rel="stylesheet" href="/assets/css/adminManageOrders.css">
    <link rel="stylesheet" href="/assets/css/adminManageProducts.css">
    <link rel="stylesheet" href="/assets/css/adminEditProduct.css">
    <link rel="stylesheet" href="/assets/css/appointment.css">
    <link rel="stylesheet" href="/assets/css/addProductForm.css">


</head>

<body class="admin-page">

    <?php require __DIR__ . '/../Admin/partials/nav.php'; ?>

    <main class="admin-content">
        <?php require __DIR__ . '/../Admin/partials/FlashMessage.php'; ?>
        <?php require $content; ?>
    </main>


</body>

</html>