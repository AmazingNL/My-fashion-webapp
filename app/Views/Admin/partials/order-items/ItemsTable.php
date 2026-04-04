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
                        $name = 'Product #' . (int) ($it['productId'] ?? 0);
                        $size = '';
                        $color = '';
                        $variant = trim($size . ' ' . $color);
                        $qty = (int) ($it['quantity'] ?? 0);
                        $price = (float) ($it['price'] ?? 0);
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
