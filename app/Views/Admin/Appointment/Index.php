<link rel="stylesheet" href="/assets/css/appointment.css">
<script defer src="/assets/js/appointment.js"></script>

<div class="appt-wrap">
    <div id="apptMsg" class="notice" hidden>
        <span id="apptMsgText"></span>
    </div>

    <div class="appt-header">
        <div class="appt-title">
            <h1><?= htmlspecialchars($title ?? 'Appointments') ?></h1>
            <p class="muted small">Manage bookings, slots, and status.</p>
        </div>
    </div>

    <div class="appt-grid">
        <div class="appt-card">
            <h3>Add Available Slot</h3>

            <form class="appt-form" action="/admin/appointments/slots/add" method="POST">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div class="row two">
                    <div>
                        <label for="appointmentDate">Date</label>
                        <input id="appointmentDate" name="appointmentDate" type="date" required>
                    </div>
                    <div>
                        <label for="startTime">Start Time</label>
                        <input id="startTime" name="startTime" type="time" required>
                    </div>
                </div>

                <div class="row two">
                    <div>
                        <label for="endTime">End Time</label>
                        <input id="endTime" name="endTime" type="time" required>
                    </div>
                    <div class="notice muted small">
                        Tip: Add a unique slot per start time to avoid duplicates.
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">Add Slot</button>
            </form>
        </div>

        <div class="appt-card" style="grid-column: span 12;">
            <h3>All Appointments</h3>

            <?php if (empty($appointments)): ?>
                <div class="notice">No appointments yet.</div>
            <?php else: ?>
                <div style="overflow:auto;">
                    <table class="table" style="width:100%; min-width:900px;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $a): ?>
                                <?php
                                $status = $a['status'] ?? 'PENDING';
                                $date = $a['appointmentDate'] ?? '';
                                $time = ($a['startTime'] ?? '') . ' - ' . ($a['endTime'] ?? '');
                                ?>
                                <tr>
                                    <td>#<?= (int) $a['appointmentId'] ?></td>
                                    <td><?= (int) $a['userId'] ?></td>
                                    <td><?= htmlspecialchars($date) ?></td>
                                    <td><?= htmlspecialchars($time) ?></td>
                                    <td>
                                        <span class="badge" data-status="<?= htmlspecialchars($status) ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="/admin/appointments/<?= (int) $a['appointmentId'] ?>/status" method="POST"
                                            style="display:flex; gap:.5rem; align-items:center;">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                                            <select name="status" required>
                                                <?php foreach (['PENDING', 'CONFIRMED', 'CANCELLED', 'COMPLETED'] as $s): ?>
                                                    <option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>>
                                                        <?= $s ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button class="btn btn-secondary" type="submit">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>