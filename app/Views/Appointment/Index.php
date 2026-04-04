
<?php
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<div class="appt-wrap">
    <?php require __DIR__ . '/partials/Notices.php'; ?>

    <div class="appt-header">
        <div class="appt-title">
            <h1><?= htmlspecialchars($title ?? 'My Appointments') ?></h1>
            <p class="muted small">Book, reschedule, or cancel your appointments.</p>
        </div>

        <div>
            <a class="btn btn-primary" href="/appointments/book">Book Appointment</a>
        </div>
    </div>

    <?php if (empty($appointments)): ?>
        <div class="notice">
            You have no appointments yet.
        </div>
    <?php else: ?>
        <?php require __DIR__ . '/partials/AppointmentGrid.php'; ?>
    <?php endif; ?>
</div>