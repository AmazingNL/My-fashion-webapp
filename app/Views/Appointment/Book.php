<link rel="stylesheet" href="/assets/css/appointment.css">
<script defer src="/assets/js/appointment.js"></script>

<div class="appt-wrap">
    <div id="apptMsg" class="notice" hidden>
        <span id="apptMsgText"></span>
    </div>

    <div class="appt-header">
        <div class="appt-title">
            <h1><?= htmlspecialchars($title ?? 'Book Appointment') ?></h1>
            <p class="muted small">Choose a date, then pick an available time slot.</p>
        </div>
        <div>
            <a class="btn btn-secondary" href="/appointments">Back</a>
        </div>
    </div>

    <form class="appt-form" action="/appointments/book" method="POST">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div class="row two">
            <div>
                <label for="apptDate">Appointment Date</label>
                <input id="apptDate" type="date" required>
                <div id="slotHelp" class="muted small" style="margin-top:.4rem;">Pick a date to load time slots.</div>
            </div>

            <div>
                <label for="slotId">Time Slot</label>
                <select id="slotId" name="slotId" required>
                    <option value="">Choose a date first</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="designType">Design Type (optional)</label>
                <input id="designType" name="designType" type="text" placeholder="e.g. Gowns / Blouse / Shorts">
            </div>
        </div>

        <div class="row">
            <div>
                <label for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Any extra details..."></textarea>
            </div>
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit">Book Appointment</button>
        </div>
    </form>
</div>