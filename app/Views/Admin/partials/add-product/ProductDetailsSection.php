<section class="card">
    <header class="card__head">
        <h2>Product Details</h2>
        <p>Core information visible in your catalog.</p>
    </header>

    <div class="fields">
        <label class="field">
            <span>Product Name</span>
            <input name="name" placeholder="Enter product name" required>
        </label>

        <label class="field">
            <span>Category</span>
            <input name="category" placeholder="e.g., Dresses, Tops, Accessories" required>
        </label>

        <div class="row2">
            <label class="field">
                <span>Price (€)</span>
                <input name="price" type="number" step="0.01" min="0" placeholder="0.00" required>
            </label>

            <label class="field">
                <span>Stock</span>
                <input name="stock" type="number" min="0" placeholder="0" required>
            </label>
        </div>

        <label class="field">
            <span>Description</span>
            <textarea name="description" placeholder="Describe your product in detail..." required></textarea>
        </label>

        <label class="field">
            <span>Product Image</span>
            <input type="file" name="image" accept="image/*">
        </label>
    </div>
</section>
