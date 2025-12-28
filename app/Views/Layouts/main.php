<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?= isset($title) ? $title . ' | Afro Store' : 'Afro Store' ?>
    </title>

    <!-- CSRF exposed ONCE, globally -->
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/addProduct.css">
    <link rel="stylesheet" href="/assets/css/product.css">
    <link rel="stylesheet" href="/assets/css/afro-base.css">
    <link rel="stylesheet" href="/assets/css/login.css">

</head>

<body>

    <?php require $content; ?>

    <script src="/assets/js/csrf-fetch.js"></script>
</body>

</html>