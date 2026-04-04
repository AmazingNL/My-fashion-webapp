
<?php
$selectedDate = (string) ($selectedDate ?? '');
$slots = $slots ?? [];
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<div class="appt-wrap">
    <?php require __DIR__ . '/partials/Notices.php'; ?>
    <?php require __DIR__ . '/partials/BookHeaderAndDateForm.php'; ?>
    <?php require __DIR__ . '/partials/BookSubmitForm.php'; ?>
</div>