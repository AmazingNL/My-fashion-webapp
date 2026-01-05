<link rel="stylesheet" href="/assets/css/main.css">

<main class="shell">
    <section class="panel">
        <div id="toast" class="toast" hidden></div>
        
        <header class="store__top">
            <div>
                <h1>Manage Orders</h1>
                <p class="muted">View and manage customer orders.</p>
            </div>
        </header>

        <section id="ordersTable" class="grid" aria-live="polite">
            <div class="empty-state">
                <div class="empty-state__icon">📦</div>
                <div class="empty-state__text">No orders yet</div>
                <p class="muted">Orders will appear here when customers make purchases.</p>
            </div>
        </section>
    </section>
</main>