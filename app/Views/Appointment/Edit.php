
<?php
$appointment = $appointment ?? [];
$selectedDate = (string) ($selectedDate ?? '');
$slots = $slots ?? [];
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
$currentSlotId = (int) ($appointment['slotId'] ?? 0);
?>

<div class="appt-wrap">
    <?php require __DIR__ . '/partials/Notices.php'; ?>

    <div class="appt-header">
        <div class="appt-title">
            <h1><?= htmlspecialchars($title ?? 'Update Appointment') ?></h1>
            <p class="muted small">Choose a new date/time, or update the details.</p>
        </div>
        <div>
            <a class="btn btn-secondary" href="/appointments">Back</a>
        </div>
    </div>

    <div class="appt-grid">
        <div class="appt-card">
            <h3>Reschedule</h3>

            <?php require __DIR__ . '/partials/EditLoadSlotsForm.php'; ?>
            <?php require __DIR__ . '/partials/EditUpdateSlotForm.php'; ?>
        </div>

        <div class="appt-card">
            <?php require __DIR__ . '/partials/EditUpdateDetailsForm.php'; ?>
        </div>
    </div>
</div>