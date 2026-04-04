<nav class="navbar">
    <div class="navbar__container">
        <a href="/productLists" class="navbar__logo">
            <span class="navbar__logo-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" focusable="false" aria-hidden="true">
                    <path d="M12 2l2.2 5.8L20 10l-5.8 2.2L12 18l-2.2-5.8L4 10l5.8-2.2L12 2z" />
                </svg>
            </span>
            <span class="navbar__logo-text">
                <span class="navbar__logo-main">Afro Elegance</span>
                <span class="navbar__logo-sub">Custom Clothing</span>
            </span>
        </a>

        <button class="navbar__toggle" aria-label="Toggle navigation menu" id="navToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="navbar__menu" id="navMenu">
            <li><a href="/productLists" class="navbar__link">Home</a></li>
            <li><a href="/appointments" class="navbar__link">Appointments</a></li>
        </ul>

        <div class="navbar__actions">
            <?php if (isset($_SESSION['userId'])): ?>
                <?php $favouriteCount = is_array($_SESSION['favourites'] ?? null) ? count($_SESSION['favourites']) : 0; ?>

                <a class="navbar__favourite" href="/favourites">
                    <span class="navbar__icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" focusable="false" aria-hidden="true">
                            <path d="M12 21s-7.2-4.35-9.43-8.09C.65 9.8 2.15 5.5 6.1 4.64c2.15-.47 4.26.35 5.9 2.15 1.64-1.8 3.75-2.62 5.9-2.15 3.95.86 5.45 5.16 3.53 8.27C19.2 16.65 12 21 12 21z" />
                        </svg>
                        <span id="favCount" class="navbar__cart-count" data-count="<?= $favouriteCount ?>"
                            <?= $favouriteCount <= 0 ? 'hidden' : '' ?>><?= $favouriteCount ?></span>
                    </span>
                    Favourites
                </a>

                <a href="/viewCart" class="navbar__cart">
                    <span class="navbar__icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                            <circle cx="9" cy="20" r="1"></circle>
                            <circle cx="18" cy="20" r="1"></circle>
                            <path d="M3 4h2l2.6 10.5a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.8L21 8H7"></path>
                        </svg>
                        <span id="cartCount" class="navbar__cart-count" hidden>0</span>
                    </span>
                    Cart
                </a>

                <div class="navbar__user">
                    <button class="navbar__user-btn" id="userMenuBtn">
                        <span class="navbar__user-avatar">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                        </span>
                        <span class="navbar__user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                    </button>
                    <div class="navbar__dropdown" id="userDropdown">
                        <a href="/orders" class="navbar__dropdown-item">My Orders</a>
                        <a href="/appointments" class="navbar__dropdown-item">My Appointments</a>
                        <hr class="navbar__dropdown-divider">
                        <form id="logoutForm" class="navbar__dropdown-item" action="/logout" method="POST" style="display:inline;">
                            <?= $this->csrfField(); ?>
                            <button id="logoutBtn" type="submit" class="navbar__dropdown-item navbar__dropdown-item--logout">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="/" class="btn btn--ghost navbar__btn">Login</a>
                <a href="/showRegistrationForm" class="btn btn--primary navbar__btn">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
