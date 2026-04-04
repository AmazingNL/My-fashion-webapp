<?php

/**
 * Manage Users - Admin page
 */

$users = $users ?? [];

?>

<?php require __DIR__ . '/partials/manage-users/Header.php'; ?>

<?php if (empty($users)): ?>
    <?php require __DIR__ . '/partials/manage-users/EmptyState.php'; ?>
<?php else: ?>
    <?php require __DIR__ . '/partials/manage-users/UsersTable.php'; ?>
<?php endif; ?>
