<div class="admin-hero admin-hero--compact">
    <div class="admin-hero__left">
        <h1 class="admin-hero__title">Order <span class="muted">#<?= $id ?></span> Items</h1>
        <p class="admin-hero__subtitle">Line items and totals for this order.</p>
    </div>

    <div class="admin-hero__right">
        <span class="statusPill" data-status="<?= htmlspecialchars($status) ?>">
            <?= htmlspecialchars($status) ?>
        </span>
        <div class="heroActions">
            <a class="btn btn-ghost" href="/admin/orders/<?= $id ?>">Open</a>
        </div>
    </div>
</div>
