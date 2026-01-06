<link rel="stylesheet" href="/assets/css/appointment.css">
<script defer src="/assets/js/appointment.js"></script>

<div class="appt-wrap">
    <div id="apptMsg" class="notice" hidden>
        <span id="apptMsgText"></span>
    </div>

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

            <form class="appt-form" action="/appointments/<?= (int) $appointmentId ?>/slot" method="POST">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div class="row two">
                    <div>
                        <label for="apptDate">New Date</label>
                        <input id="apptDate" type="date" required>
                        <div id="slotHelp" class="muted small" style="margin-top:.4rem;">Pick a date to load available
                            times.</div>
                    </div>

                    <div>
                        <label for="slotId">New Slot</label>
                        <select id="slotId" name="slotId" required>
                            <option value="">Choose a date first</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">Update Slot</button>
            </form>
        </div>

        <div class="appt-card">
            <h3>Update Details</h3>

            <form class="appt-form" action="/appointments/<?= (int) $appointmentId ?>/save" method="POST">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div class="row">
                    <label for="designType">Design Type</label>
                    <input id="designType" name="designType" type="text"
                        placeholder="e.g. Braids / Nails / Wig install">
                </div>

                <div class="row">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Any extra details..."></textarea>
                </div>

                <button class="btn btn-secondary" type="submit">Save Details</button>
            </form>
        </div>
    </div>
</div>