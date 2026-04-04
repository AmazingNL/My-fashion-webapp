<h3>Update Details</h3>

<form class="appt-form" action="/appointments/<?= (int) $appointmentId ?>/save" method="POST">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="row">
        <label for="designType">Design Type</label>
        <input id="designType" name="designType" type="text"
            placeholder="e.g. Braids / Nails / Wig install"
            value="<?= htmlspecialchars((string) ($appointment['designType'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="row">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4" placeholder="Any extra details..."><?= htmlspecialchars((string) ($appointment['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <button class="btn btn-secondary" type="submit">Save Details</button>
</form>
