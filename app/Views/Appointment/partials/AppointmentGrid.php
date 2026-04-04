<div class="appt-grid">
    <?php foreach ($appointments as $a): ?>
        <?php
        $status = strtoupper((string) ($a['status'] ?? 'PENDING'));
        $date = (string) ($a['appointmentDate'] ?? '');
        $time = (string) (($a['startTime'] ?? '') . ' - ' . ($a['endTime'] ?? ''));
        ?>
        <div class="appt-card">
            <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <h3>Appointment #<?= (int) ($a['appointmentId'] ?? 0) ?></h3>

                <span class="badge" data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="appt-meta">
                <div class="item">
                    <div class="muted small">Date</div>
                    <div><?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="item">
                    <div class="muted small">Time</div>
                    <div><?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="item">
                    <div class="muted small">Design Type</div>
                    <div><?= htmlspecialchars((string) ($a['designType'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>

            <?php if (!empty($a['notes'])): ?>
                <p class="muted small" style="margin-top:.75rem;">
                    <strong>Notes:</strong> <?= htmlspecialchars((string) $a['notes'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <div class="appt-actions">
                <a class="btn btn-secondary" href="/appointments/<?= (int) ($a['appointmentId'] ?? 0) ?>/edit">Update</a>

                <form action="/appointments/<?= (int) ($a['appointmentId'] ?? 0) ?>/cancel" method="POST" style="margin:0;">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-danger" type="submit" onclick="return confirm('Cancel this appointment?')">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
