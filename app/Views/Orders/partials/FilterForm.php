<form class="card orders__toolbar" method="get" action="/orders">
    <div class="field">
        <label for="statusFilter">Filter by status</label>
        <select id="statusFilter" name="status">
            <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>All</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
            <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Completed</option>
            <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>

    <div class="field">
        <label for="searchOrders">Search</label>
        <input id="searchOrders" name="q" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Order #, status, payment..." />
    </div>
    <button class="btn btn--primary" type="submit">Apply</button>

</form>