<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? "Login" ?></title>

    <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="/assets/css/main.css">

</head>

<body>
    <main class="main-content">
        <?php require $content; ?>
    </main>
    <?php if (!empty($pageScript)): ?>
        <script src="/assets/js/<?= htmlspecialchars($pageScript, ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php endif; ?>



</body>

</html>