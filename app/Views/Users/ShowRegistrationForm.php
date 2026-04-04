<?php
// views/users/showRegistrationForm.php
?>

<?php
$errors = $errors ?? [];
$oldInput = $oldInput ?? [];
?>

<section class="auth">
    <div class="auth__wrap">
        <?php require __DIR__ . '/partials/register/BrandPanel.php'; ?>
        <?php require __DIR__ . '/partials/register/FormCard.php'; ?>
    </div>
</section>