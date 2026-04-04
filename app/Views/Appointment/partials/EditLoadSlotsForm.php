<form class="appt-form" action="/appointments/<?= (int) $appointmentId ?>/edit" method="GET">
    <div class="row two">
        <div>
            <label for="apptDateLoad">New Date</label>
            <input id="apptDateLoad" name="date" type="date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>" required>
            <div class="muted small" style="margin-top:.4rem;">Select date and load available times.</div>
        </div>
        <div style="display:flex; align-items:flex-end;">
            <button class="btn btn-secondary" type="submit">Load Slots</button>
        </div>
    </div>
</form>
