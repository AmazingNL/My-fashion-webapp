<?php
/** @var \App\Models\Product $product */
/** @var \App\Models\ProductVariant[] $variant */
?>

<body>
    <main class="shell">
        <article class="panel product" data-product-id="<?= (int) $product->getId(); ?>" data-variants='<?= htmlspecialchars(json_encode(array_map(fn($v) => [
               "variantId" => $v->getVariantId(),
               "size" => $v->getSize(),
               "color" => $v->getColor(),
               "stock" => $v->getStock()
           ], $variants)), ENT_QUOTES, "UTF-8"); ?>'>
            <header class="product__head">
                <a class="btn btn--ghost" href="/products">← Back</a>

                <button class="heart" id="favBtn" type="button" aria-pressed="false" title="Add to favourites">
                    ♥
                </button>
            </header>

            <section class="product__body">
                <figure class="product__media">
                    <img src="<?= htmlspecialchars($product->getImage()); ?>"
                        alt="<?= htmlspecialchars($product->getName()); ?>">
                </figure>

                <section class="product__info">
                    <h1><?= htmlspecialchars($product->getName()); ?></h1>
                    <p class="muted"><?= htmlspecialchars($product->getDescription()); ?></p>

                    <p class="price">€<?= number_format($product->getPrice(), 2); ?></p>

                    <form class="buy" id="buyForm">
                        <?= $this->csrfField(); ?>
                        <fieldset class="pick">
                            <legend>Choose options</legend>

                            <label class="label" for="size">Size</label>
                            <select id="size" required>
                                <option value="">Select size…</option>
                                <?php foreach ($sizes as $s): ?>
                                    <option value="<?= htmlspecialchars($s); ?>"><?= htmlspecialchars($s); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label class="label" for="color">Colour</label>
                            <select id="color" required>
                                <option value="">Select colour…</option>
                                <?php foreach ($colors as $c): ?>
                                    <option value="<?= htmlspecialchars($c); ?>"><?= htmlspecialchars($c); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <p id="stockMsg" class="muted">Pick size + colour to see stock.</p>
                        </fieldset>

                        <div class="buy__row">
                            <label class="label" for="qty">Qty</label>
                            <input id="qty" type="number" min="1" value="1" />
                            <button class="btn" id="addBtn" type="submit" disabled>Add to basket</button>
                        </div>

                        <p id="toast" class="toast" hidden></p>
                    </form>
                </section>
            </section>
        </article>
    </main>
    <script defer src="/assets/js/productDetail.js"></script>

</body>