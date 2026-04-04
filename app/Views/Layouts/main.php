<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/partials/main/Head.php'; ?>

<body>
    <?php require __DIR__ . '/partials/main/Navbar.php'; ?>

    <main class="main-content">
        <?php require $content; ?>
    </main>

    <?php require __DIR__ . '/partials/main/Footer.php'; ?>
    <?php require __DIR__ . '/partials/main/Scripts.php'; ?>

</body>

</html>