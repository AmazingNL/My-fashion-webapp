<?php
$error = (string) ($_GET['error'] ?? '');
$success = (string) ($_GET['success'] ?? '');
$email = (string) ($_GET['email'] ?? '');
?>

<main class="af-shell">
    <section class="af-wrap login-layout">
        <?php require __DIR__ . '/partials/forgot-password/BrandPanel.php'; ?>
        <?php require __DIR__ . '/partials/forgot-password/FormCard.php'; ?>
    </section>
</main>