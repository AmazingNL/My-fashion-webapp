<div class="grid grid--3">
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-state__icon">🧺</div>
            <div class="empty-state__text">No orders yet</div>
            <a class="btn btn--primary" href="/productLists">Browse products</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php
            $id = (int) ($order->orderId ?? 0);
            $status = strtolower((string) ($order->status?->value ?? 'unknown'));
            $paymentStatus = strtolower((string) ($order->paymentStatus?->value ?? 'pending'));
            $totalAmount = (float) ($order->totalAmount ?? 0);
            $createdAt = (string) ($order->createdAt ?? '-');
            ?>
            <article class="card orders__card">
                <div class="orders__cardTop">
                    <div class="orders__id">Order #<?= $id ?></div>
                    <div class="orders__badges">
                        <span class="pill"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="pill">pay: <?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <div class="orders__meta">
                    <div><span class="orders__label">Total</span><strong>€<?= htmlspecialchars(number_format($totalAmount, 2), ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div><span class="orders__label">Created</span><strong><?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></strong></div>
                </div>

                <div class="orders__cardActions">
                    <a class="btn btn--ghost" href="/orders/<?= $id ?>">View</a>
                    <?php if ($canCancel($status)): ?>
                        <form method="post" action="/orders/<?= $id ?>/cancel" style="display:inline">
                            <?= $this->csrfField() ?>
                            <button class="btn btn--danger" type="submit">Cancel</button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn--danger" type="button" disabled>Cancel</button>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>