<?php
$products = $products ?? [];
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<main class="fav">
    <?php require __DIR__ . '/partials/FavouritesHeader.php'; ?>

    <section class="fav__notices">
        <?php if ($error !== ''): ?>
            <div class="notice notice--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <div class="notice notice--success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </section>

    <?php require __DIR__ . '/partials/FavouritesGrid.php'; ?>
</main>