<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? "Login" ?></title>

    <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/login.css">
</head>

<body>
    <main class="main-content">
        <?php require $content; ?>
    </main>
    <script src="/assets/js/csrf-fetch.js"></script>

</body>

</html>