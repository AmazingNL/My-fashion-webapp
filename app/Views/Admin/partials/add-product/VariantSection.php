<section class="card">
    <header class="card__head">
        <h2>Variant</h2>
        <p>Add one variant now. You can add more from edit page later.</p>
    </header>

    <div class="fields">
        <label class="field">
            <span>Size</span>
            <input name="variants[0][size]" placeholder="e.g., S, M, L" required>
        </label>

        <label class="field">
            <span>Color</span>
            <input name="variants[0][colour]" placeholder="e.g., Red, Blue" required>
        </label>

        <label class="field">
            <span>Variant Stock</span>
            <input name="variants[0][stockQuantity]" type="number" min="0" placeholder="0" required>
        </label>
    </div>
</section>
