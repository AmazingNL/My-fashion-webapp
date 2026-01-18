<?php
/** @var array $orders */ // array of Order objects
$csrf = $_SESSION['csrf'] ?? '';
?>

<link rel="stylesheet" href="/assets/css/adminManageOrders.css">

<div class="admin-shell">

    <div class="admin-hero">
        <div>
            <h1>Manage Orders</h1>
            <p>Update order status and view details/items.</p>
        </div>
    </div>

    <div id="ordersAlert" class="admin-alert" style="display:none;"></div>

    <!-- CSRF for JS (matches your JS style) -->
    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrf) ?>">

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
                            $id = (int) $o->getOrderId();
                            $status = strtolower((string) $o->getStatus());
                            $total = (float) $o->getTotalAmount();
                            $created = (string) $o->getCreatedAt();
                            ?>
                            <tr data-order-row="<?= $id ?>">
                                <td class="mono">#<?= $id ?></td>

                                <td>
                                    <div class="statusRow">
                                        <select class="statusSelect" data-order-id="<?= $id ?>"
                                            data-original-status="<?= htmlspecialchars($status) ?>"
                                            aria-label="Update status for order #<?= $id ?>">
                                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>pending</option>
                                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>processing
                                            </option>
                                            <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>shipped</option>
                                            <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>delivered
                                            </option>
                                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>cancelled
                                            </option>
                                        </select>

                                        <span class="statusPill" data-status-pill="<?= $id ?>"
                                            data-status="<?= htmlspecialchars($status) ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>

                                        <span class="tiny" data-status-spinner="<?= $id ?>"
                                            style="display:none;">Updating…</span>
                                    </div>
                                </td>

                                <td>€<?= htmlspecialchars(number_format($total, 2)) ?></td>
                                <td><?= htmlspecialchars($created) ?></td>

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

</div>
