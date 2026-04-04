
<?php
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<main class="shell">
    <section class="panel" aria-labelledby="page-title">
        <?php require __DIR__ . '/partials/add-product/Header.php'; ?>

        <form action="/admin/addProduct" method="POST" enctype="multipart/form-data">
            <?= $this->csrfField(); ?>

            <div class="grid">
                <?php require __DIR__ . '/partials/add-product/ProductDetailsSection.php'; ?>
                <?php require __DIR__ . '/partials/add-product/VariantSection.php'; ?>
            </div>

            <?php require __DIR__ . '/partials/add-product/FormActions.php'; ?>
        </form>
    </section>
</main>