<div id="productPagination">
    <?php if ($productListVm->getTotalPages() > 1): ?>
        <?php
        $baseParams = [];
        $search = trim((string) ($currentFilters['search'] ?? ''));
        $category = trim((string) ($currentFilters['category'] ?? ''));
        $minPrice = $currentFilters['minPrice'] ?? null;
        $maxPrice = $currentFilters['maxPrice'] ?? null;
        $pageSize = (int) ($currentFilters['pageSize'] ?? 12);

        if ($search !== '') {
            $baseParams['search'] = $search;
        }
        if ($category !== '') {
            $baseParams['category'] = $category;
        }
        if ($minPrice !== null && $minPrice !== '') {
            $baseParams['minPrice'] = $minPrice;
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $baseParams['maxPrice'] = $maxPrice;
        }
        $baseParams['pageSize'] = $pageSize > 0 ? $pageSize : 12;

        $pageUrl = static function (int $page) use ($baseParams): string {
            $params = $baseParams;
            $params['page'] = $page;
            return '/productLists?' . http_build_query($params);
        };
        ?>
        <nav class="pagination" aria-label="Product pages">
            <div class="pagination__meta">
                Showing <?= $productListVm->getStartItem() ?>-<?= $productListVm->getEndItem() ?> of <?= $productListVm->getTotalCount() ?> products
            </div>

            <div class="pagination__controls">
                <?php if ($productListVm->hasPreviousPage()): ?>
                    <a class="btn btn--ghost" href="<?= htmlspecialchars($pageUrl($productListVm->getPreviousPage()), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $productListVm->getTotalPages(); $page++): ?>
                    <a
                        class="btn <?= $page === $productListVm->getCurrentPage() ? 'btn--secondary' : 'btn--ghost' ?>"
                        href="<?= htmlspecialchars($pageUrl($page), ENT_QUOTES, 'UTF-8') ?>"
                        aria-current="<?= $page === $productListVm->getCurrentPage() ? 'page' : 'false' ?>"
                    >
                        <?= $page ?>
                    </a>
                <?php endfor; ?>

                <?php if ($productListVm->hasNextPage()): ?>
                    <a class="btn btn--ghost" href="<?= htmlspecialchars($pageUrl($productListVm->getNextPage()), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
</div>
