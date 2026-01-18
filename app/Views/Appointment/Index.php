<link rel="stylesheet" href="/assets/css/appointment.css">
<script defer src="/assets/js/appointment.js"></script>

<div class="appt-wrap">
    <div id="apptMsg" class="notice" hidden>
        <span id="apptMsgText"></span>
    </div>

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
        <div class="appt-grid">
            <?php foreach ($appointments as $a): ?>
                <?php
                $status = $a['status'] ?? 'PENDING';
                $date = $a['appointmentDate'] ?? '';
                $time = ($a['startTime'] ?? '') . ' - ' . ($a['endTime'] ?? '');
                ?>
                <div class="appt-card">
                    <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                        <h3>Appointment #<?= (int) $a['appointmentId'] ?></h3>
                        <?php
                        $status = strtoupper((string) ($a['status'] ?? 'PENDING'));
                        ?>

                        <span class="badge" data-status="<?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>

                    </div>

                    <div class="appt-meta">
                        <div class="item">
                            <div class="muted small">Date</div>
                            <div><?= htmlspecialchars($date) ?></div>
                        </div>
                        <div class="item">
                            <div class="muted small">Time</div>
                            <div><?= htmlspecialchars($time) ?></div>
                        </div>
                        <div class="item">
                            <div class="muted small">Design Type</div>
                            <div><?= htmlspecialchars($a['designType'] ?? '-') ?></div>
                        </div>
                    </div>

                    <?php if (!empty($a['notes'])): ?>
                        <p class="muted small" style="margin-top:.75rem;">
                            <strong>Notes:</strong> <?= htmlspecialchars($a['notes']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="appt-actions">
                        <a class="btn btn-secondary" href="/appointments/<?= (int) $a['appointmentId'] ?>/edit">Update</a>

                        <form action="/appointments/<?= (int) $a['appointmentId'] ?>/cancel" method="POST" style="margin:0;">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button class="btn btn-danger" type="submit" data-appt-cancel>
                                Cancel
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>