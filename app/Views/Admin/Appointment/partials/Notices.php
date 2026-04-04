<?php
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<?php if ($error !== ''): ?>
    <div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="notice success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
