<form class="appt-form" action="/appointments/<?= (int) $appointmentId ?>/slot" method="POST">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>">

    <div class="row two">
        <div>
            <label>Selected Date</label>
            <input type="text" value="<?= htmlspecialchars($selectedDate !== '' ? $selectedDate : 'Please select a date first', ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>

        <div>
            <label for="slotId">New Slot</label>
            <select id="slotId" name="slotId" required>
                <?php if ($selectedDate === ''): ?>
                    <option value="">Choose a date first</option>
                <?php elseif (empty($slots)): ?>
                    <option value="">No available slots for selected date</option>
                <?php else: ?>
                    <option value="">Choose a time</option>
                    <?php foreach ($slots as $slot): ?>
                        <?php $slotId = (int) ($slot->slotId ?? 0); ?>
                        <option value="<?= $slotId ?>" <?= $slotId === $currentSlotId ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($slot->startTime ?? ''), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string) ($slot->endTime ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary" type="submit">Update Slot</button>
</form>
