<div class="admin-card">
    <div class="admin-tableWrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ORDER</th>
                    <th>STATUS</th>
                    <th>TOTAL</th>
                    <th>CREATED</th>
                    <th class="admin-actions">ACTION</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="5" style="padding:16px;">No orders found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <?php
                        $id = (int) ($o->orderId ?? 0);
                        $status = strtolower((string) (($o->status?->value) ?? $o->status ?? 'pending'));
                        $total = (float) ($o->totalAmount ?? 0);
                        $created = (string) ($o->createdAt ?? '');
                        ?>
                        <tr>
                            <td class="mono">#<?= $id ?></td>

                            <td>
                                <form class="statusRow" method="post" action="/admin/orders/<?= $id ?>/status">
                                    <?= $this->csrfField() ?>
                                    <select name="status" aria-label="Update status for order #<?= $id ?>">
                                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>pending</option>
                                        <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>processing</option>
                                        <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>shipped</option>
                                        <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>delivered</option>
                                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                                    </select>
                                    <button class="btn btn-ghost" type="submit">Save</button>
                                    <span class="statusPill" data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </form>
                            </td>

                            <td>€<?= htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($created, ENT_QUOTES, 'UTF-8') ?></td>

                            <td class="admin-actions">
                                <a class="btn btn-ghost" href="/admin/orders/<?= $id ?>">Open</a>
                                <a class="btn btn-solid" href="/admin/orders/<?= $id ?>/items">Items</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
