<?php
$error = (string) ($_GET['error'] ?? '');
$success = (string) ($_GET['success'] ?? '');
?>

<main class="af-shell">
    <section class="af-wrap login-layout">
        <?php require __DIR__ . '/partials/reset-code/BrandPanel.php'; ?>
        <?php require __DIR__ . '/partials/reset-code/FormCard.php'; ?>
    </section>
</main>