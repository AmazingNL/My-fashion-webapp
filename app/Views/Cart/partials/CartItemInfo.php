<div class="cartItemTop">
    <div class="cartItemInfo">
        <h3 class="cartItemName"><?= htmlspecialchars($name) ?></h3>

        <p class="cartItemMeta">
            <?php if ($size): ?><span class="chip">Size: <?= htmlspecialchars($size) ?></span><?php endif; ?>
            <?php if ($color): ?><span class="chip">Color: <?= htmlspecialchars($color) ?></span><?php endif; ?>
            <span class="pill pill--stock">
                Stock: <?= (int) ($item['stockQuantity'] ?? 0) ?>
            </span>
        </p>
    </div>
</div>
