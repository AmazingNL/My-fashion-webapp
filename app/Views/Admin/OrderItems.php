<?php
/** @var \App\Models\Order $order */
/** @var array $items */

$id = (int) $order->getOrderId();
$status = strtolower((string) $order->getStatus());

$itemsTotal = 0.0;
foreach ($items as $it) {
    $itemsTotal += ((int) $it->getQuantity() * (float) $it->getPrice());
}
?>

<link rel="stylesheet" href="/assets/css/adminManageOrders.css">

<div class="admin-shell">

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

    <div class="orderItemsPage">
        <!-- LEFT -->
        <section class="orderItemsCard">
            <header class="orderItemsHeader">
                <div>
                    <h2 class="orderItemsTitle">Items</h2>
                    <p class="orderItemsHint">Products included in this order</p>
                </div>
                <span class="orderItemsCount"><?= count($items) ?> item(s)</span>
            </header>

            <div class="orderItemsTableWrap">
                <table class="orderItemsTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Variant</th>
                            <th class="right">Qty</th>
                            <th class="right">Price</th>
                            <th class="right">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="5" class="emptyRow">No items found for this order.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $it): ?>
                                <?php
                                $name = (string) ($it->getProductName() ?? 'Product');
                                $size = (string) ($it->getVariantSize() ?? '');
                                $color = (string) ($it->getVariantColor() ?? '');
                                $variant = trim($size . ' ' . $color);
                                $qty = (int) $it->getQuantity();
                                $price = (float) $it->getPrice();
                                $line = $qty * $price;
                                ?>
                                <tr>
                                    <td>
                                        <div class="pCell">
                                            <div class="pThumb" aria-hidden="true"></div>
                                            <div class="pInfo">
                                                <div class="pName"><?= htmlspecialchars($name) ?></div>
                                                <div class="pMeta">Order #<?= $id ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="pChip"><?= htmlspecialchars($variant !== '' ? $variant : '-') ?></span>
                                    </td>

                                    <td class="right mono"><?= $qty ?></td>
                                    <td class="right">€<?= htmlspecialchars(number_format($price, 2)) ?></td>
                                    <td class="right strong">€<?= htmlspecialchars(number_format($line, 2)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- RIGHT -->
        <aside class="orderSummaryCard">
            <h2 class="orderSummaryTitle">Summary</h2>

            <div class="orderSummaryBox">
                <div class="sRow">
                    <span>Items total</span>
                    <strong>€<?= htmlspecialchars(number_format($itemsTotal, 2)) ?></strong>
                </div>
                <div class="sRow muted">
                    <span>Shipping</span>
                    <span>€0.00</span>
                </div>
                <div class="sRow muted">
                    <span>Tax</span>
                    <span>€0.00</span>
                </div>

                <div class="sDivider"></div>

                <div class="sRow total">
                    <span>Total</span>
                    <strong>€<?= htmlspecialchars(number_format((float) $order->getTotalAmount(), 2)) ?></strong>
                </div>
            </div>

            <a class="orderSummaryBtn" href="/admin/orders/<?= $id ?>">Back to Order</a>
        </aside>
    </div>

</div>