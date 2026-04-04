<?php if ($error !== ''): ?>
    <div class="admin-alert admin-alert--error" style="display:block;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="admin-alert admin-alert--success" style="display:block;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
