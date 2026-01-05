<?php
/** @var string $title */
/** @var array $cartItems */
/** @var float|int $total */
/** @var int $itemCount */
/** @var bool $isEmpty */
?>

<link rel="stylesheet" href="/assets/css/cart.css">

<main class="cartShell" data-page="cart">
    <header class="cartHeader">
        <div>
            <h1 class="cartTitle"><?= htmlspecialchars($title ?? 'Shopping Cart') ?></h1>
            <p class="cartSub">
                <span id="cartCountText"><?= (int) ($itemCount ?? 0) ?></span> item(s) in your basket
            </p>
        </div>

        <div class="cartHeaderActions">
            <a class="btn btnGhost" href="/products">Continue shopping</a>
            <button class="btn btnDanger" id="clearCartBtn" type="button" <?= !empty($isEmpty) ? 'disabled' : '' ?>>
                Clear cart
            </button>
        </div>
    </header>

    <section class="cartNotice" id="cartNotice" hidden></section>

    <?php if (!empty($isEmpty)): ?>
        <section class="cartEmpty">
            <div class="cartEmptyCard">
                <h2>Your cart is empty 🛍️</h2>
                <p>Add something and it'll appear here.</p>
                <a class="btn" href="/products">Browse products</a>
            </div>
        </section>
    <?php else: ?>

        <section class="cartGrid">
            <div class="cartItems" id="cartItems">
                <?php foreach (($cartItems ?? []) as $item): ?>
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

                    <article class="cartItem" data-product-id="<?= $productId ?>" data-variant-id="<?= $variantId ?>"
                        data-unit-price="<?= htmlspecialchars((string) $price) ?>">
                        <div class="cartItemMedia">
                            <?php if ($img): ?>
                                <img class="cartItemImg" src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>">
                            <?php else: ?>
                                <div class="cartItemImgPh" aria-hidden="true">No image</div>
                            <?php endif; ?>
                        </div>

                        <div class="cartItemMain">
                            <div class="cartItemTop">
                                <div class="cartItemInfo">
                                    <h3 class="cartItemName"><?= htmlspecialchars($name) ?></h3>

                                    <p class="cartItemMeta">
                                        <?php if ($size): ?><span class="chip">Size:
                                                <?= htmlspecialchars($size) ?></span><?php endif; ?>
                                        <?php if ($color): ?><span class="chip">Color:
                                                <?= htmlspecialchars($color) ?></span><?php endif; ?>
                                        <span class="pill pill--stock">
                                            Stock: <?= (int) ($item['stockQuantity'] ?? 0) ?>
                                        </span>

                                    </p>
                                </div>

                                <button class="iconBtn removeBtn" type="button" title="Remove item" aria-label="Remove item">
                                    ✕
                                </button>
                            </div>

                            <div class="cartItemBottom">
                                <div class="qtyBox" role="group" aria-label="Quantity controls">
                                    <button class="qtyBtn decBtn" type="button" aria-label="Decrease quantity">−</button>
                                    <input class="qtyInput" type="number" min="1" step="1" value="<?= $qty ?>"
                                        inputmode="numeric">
                                    <button class="qtyBtn incBtn" type="button" aria-label="Increase quantity">+</button>
                                </div>

                                <div class="cartPrices">
                                    <div class="unitPrice">€ <span><?= number_format($price, 2) ?></span></div>
                                    <div class="lineTotal">
                                        Line total: € <strong
                                            class="lineTotalValue"><?= number_format($lineTotal, 2) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="cartSummary" aria-label="Cart summary">
                <div class="summaryCard">
                    <h2>Summary</h2>

                    <div class="summaryRow">
                        <span>Items</span>
                        <span id="summaryItemCount"><?= (int) ($itemCount ?? 0) ?></span>
                    </div>

                    <div class="summaryRow summaryTotal">
                        <span>Total</span>
                        <span>€ <strong id="summaryTotal"><?= number_format((float) ($total ?? 0), 2) ?></strong></span>
                    </div>

                    <button class="btn btnWide" id="checkoutBtn" type="button" <?= !empty($isEmpty) ? 'disabled' : '' ?>>
                        Checkout
                    </button>
                </div>
            </aside>
        </section>

    <?php endif; ?>
</main>
<script defer src="/assets/js/cart.js?v=<?= time() ?>"></script>
