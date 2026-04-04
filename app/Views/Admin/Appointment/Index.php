
<?php
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<div class="appt-wrap">
    <div class="appt-header">
        <div class="appt-title">
            <h1><?= htmlspecialchars($title ?? 'Appointments') ?></h1>
            <p class="muted small">Manage bookings, slots, and status.</p>
        </div>
    </div>

    <div class="appt-grid">
        <?php require __DIR__ . '/partials/AddSlotForm.php'; ?>
        <?php require __DIR__ . '/partials/AppointmentsTable.php'; ?>
    </div>
</div>