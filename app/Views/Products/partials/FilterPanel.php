<?php
$filterCategories = $filterCategories ?? [];
$currentFilters = $currentFilters ?? [];
$selectedCategories = array_values(array_filter(array_map('trim', explode(',', (string) ($currentFilters['category'] ?? '')))));
?>

<aside id="filterPanel" class="filters">
    <form method="get" action="/productLists">
        <div class="filters__header">
            <h2 class="filters__title">Filter</h2>
            <button id="filterClose" class="btn btn--ghost filters__close" type="button">Close</button>
        </div>

        <div class="filters__section">
            <h3 class="filters__heading">Search</h3>
            <div class="field">
                <label for="searchInput">Search products</label>
                <input
                    id="searchInput"
                    name="search"
                    type="search"
                    placeholder="Search by name, category, or description"
                    value="<?= htmlspecialchars((string) ($currentFilters['search'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>
        </div>

        <div class="filters__section">
            <h3 class="filters__heading">Category</h3>
            <div id="categoryFilters" class="filters__list">
                <?php if (empty($filterCategories)): ?>
                    <p class="muted">No categories available</p>
                <?php else: ?>
                    <?php foreach ($filterCategories as $category): ?>
                        <?php
                        $isChecked = in_array(strtolower($category), array_map('strtolower', $selectedCategories), true);
                        ?>
                        <label class="filters__item">
                            <input name="category[]" type="checkbox" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" <?= $isChecked ? 'checked' : '' ?>>
                            <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="filters__section">
            <h3 class="filters__heading">Price</h3>

            <div class="filters__row">
                <div class="field">
                    <label for="minPrice">Min (€)</label>
                    <input id="minPrice" name="minPrice" type="number" min="0" placeholder="0" value="<?= htmlspecialchars((string) ($currentFilters['minPrice'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="field">
                    <label for="maxPrice">Max (€)</label>
                    <input id="maxPrice" name="maxPrice" type="number" min="0" placeholder="500" value="<?= htmlspecialchars((string) ($currentFilters['maxPrice'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
        </div>

        <div class="filters__actions">
            <input type="hidden" name="pageSize" value="<?= (int) ($currentFilters['pageSize'] ?? 12) ?>">
            <button class="btn btn--secondary" type="submit">Apply Filters</button>
            <a class="btn btn--ghost" href="/productLists">Clear Filters</a>
        </div>
    </form>
</aside>