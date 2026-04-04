<form class="appt-form" action="/appointments/book" method="POST">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>">

    <div class="row">
        <div class="muted small">
            Selected date: <?= htmlspecialchars($selectedDate !== '' ? $selectedDate : 'none', ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>

    <div class="row">
        <div>
            <label for="slotId">Time Slot</label>
            <select id="slotId" name="slotId" required>
                <?php if ($selectedDate === ''): ?>
                    <option value="">Choose a date first</option>
                <?php elseif (empty($slots)): ?>
                    <option value="">No available slots for selected date</option>
                <?php else: ?>
                    <option value="">Choose a time</option>
                    <?php foreach ($slots as $slot): ?>
                        <option value="<?= (int) ($slot->slotId ?? 0) ?>">
                            <?= htmlspecialchars((string) ($slot->startTime ?? ''), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string) ($slot->endTime ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
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
