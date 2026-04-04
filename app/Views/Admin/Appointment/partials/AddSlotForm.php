<div class="appt-card">
    <h3>Add Available Slot</h3>

    <form class="appt-form" action="/admin/appointments/slots/add" method="POST">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <div class="row two">
            <div>
                <label for="appointmentDate">Date</label>
                <input id="appointmentDate" name="appointmentDate" type="date" required>
            </div>

        </div>

        <div class="row two">
            <div>
                <label for="startTime">Start Time</label>
                <input id="startTime" name="startTime" type="time" required>
            </div>
            <div>
                <label for="endTime">End Time</label>
                <input id="endTime" name="endTime" type="time" required>
            </div>

        </div>


        <button class="btn btn-primary" type="submit">Add Slot</button>
    </form>
</div>