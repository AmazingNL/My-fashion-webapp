<div class="appt-header">
    <div class="appt-title">
        <h1><?= htmlspecialchars($title ?? 'Book Appointment') ?></h1>
        <p class="muted small">Choose a date, then pick an available time slot.</p>
    </div>
    <div>
        <a class="btn btn-secondary" href="/appointments">Back</a>
    </div>
</div>

<form class="appt-form" action="/appointments/book" method="GET">
    <div class="row two">
        <div>
            <label for="apptDate">Appointment Date</label>
            <input id="apptDate" name="date" type="date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>" required>
            <div class="muted small" style="margin-top:.4rem;">Select a date and load available slots.</div>
        </div>

        <div style="display:flex; align-items:flex-end;">
            <button class="btn btn-secondary" type="submit">Load Slots</button>
        </div>
    </div>
</form>
