<div class="cartItemMedia">
    <?php if ($img): ?>
        <img class="cartItemImg" src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>">
    <?php else: ?>
        <div class="cartItemImgPh" aria-hidden="true">No image</div>
    <?php endif; ?>
</div>
