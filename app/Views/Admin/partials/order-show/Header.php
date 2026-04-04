<div class="admin-hero admin-hero--compact">
    <div class="admin-hero__left">
        <h1 class="admin-hero__title">Order <span class="muted">#<?= $id ?></span></h1>
        <p class="admin-hero__subtitle">Overview, payment, addresses, and quick actions.</p>
    </div>

    <div class="admin-hero__right">
        <span class="statusPill" data-status="<?= htmlspecialchars($status) ?>">
            <?= htmlspecialchars($status) ?>
        </span>
    </div>
</div>
