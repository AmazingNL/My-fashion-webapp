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
                        $status = (string) ($a['status'] ?? 'PENDING');
                        $date = (string) ($a['appointmentDate'] ?? '');
                        $time = (string) (($a['startTime'] ?? '') . ' - ' . ($a['endTime'] ?? ''));
                        ?>
                        <tr>
                            <td>#<?= (int) ($a['appointmentId'] ?? 0) ?></td>
                            <td><?= (int) ($a['userId'] ?? 0) ?></td>
                            <td><?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge" data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <form action="/admin/appointments/<?= (int) ($a['appointmentId'] ?? 0) ?>/status" method="POST"
                                    style="display:flex; gap:.5rem; align-items:center;">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

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
