<?php
$title = isset($cartVm) ? $cartVm->getTitle() : (string) ($title ?? 'Shopping Cart');
$cartItems = isset($cartVm) ? $cartVm->getCartItems() : (array) ($cartItems ?? []);
$total = isset($cartVm) ? $cartVm->getTotal() : (float) ($total ?? 0);
$itemCount = isset($cartVm) ? $cartVm->getItemCount() : (int) ($itemCount ?? 0);
$isEmpty = isset($cartVm) ? $cartVm->isEmpty() : empty($cartItems);

$noticeMessage = isset($cartVm) ? $cartVm->getNoticeMessage() : (string) ($noticeMessage ?? '');
$noticeType = isset($cartVm) ? $cartVm->getNoticeType() : (string) ($noticeType ?? 'success');
$noticeClass = 'cartNotice ' . ($noticeType === 'error' ? 'cartNotice--error' : 'cartNotice--success');
?>

<main class="cartShell">
    <?php require __DIR__ . '/partials/CartHeader.php'; ?>

    <section class="<?= htmlspecialchars($noticeClass, ENT_QUOTES, 'UTF-8') ?>" <?= $noticeMessage === '' ? 'hidden' : '' ?>>
        <?= htmlspecialchars($noticeMessage, ENT_QUOTES, 'UTF-8') ?>
    </section>

    <?php if (!empty($isEmpty)): ?>
        <?php require __DIR__ . '/partials/CartEmptyState.php'; ?>
    <?php else: ?>
        <section class="cartGrid">
            <div class="cartItems">
            <?php foreach ($cartItems as $item): ?>
                    <?php
                    $productId = (int) ($item['productId'] ?? 0);
                    $variantId = (int) ($item['variantId'] ?? 0);

                    $name = (string) ($item['name'] ?? 'Product');
                    $img = (string) ($item['image'] ?? '');
                    $price = (float) ($item['price'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 1);

                    $size = (string) ($item['size'] ?? '');
                    $color = (string) ($item['color'] ?? ($item['colour'] ?? ''));

                    $lineTotal = $price * $qty;
                    ?>

                    <article class="cartItem">
                        <?php require __DIR__ . '/partials/CartItemImage.php'; ?>

                        <div class="cartItemMain">
                            <?php require __DIR__ . '/partials/CartItemInfo.php'; ?>

                            <?php require __DIR__ . '/partials/CartItemActions.php'; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php require __DIR__ . '/partials/CartSummary.php'; ?>
        </section>

    <?php endif; ?>
</main>
