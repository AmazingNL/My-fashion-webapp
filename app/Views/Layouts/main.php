<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= isset($title) ? $title . ' | Afro Elegance' : 'Afro Elegance - Custom Female Clothing' ?>
    </title>

    <!-- CSRF exposed ONCE, globally -->
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="/assets/css/productDetails.css">

</head>

<body>
    <!-- ============================================
         NAVIGATION BAR
         ============================================ -->
    <nav class="navbar">
        <div class="navbar__container">
            <!-- Logo -->
            <a href="/products" class="navbar__logo">
                <span class="navbar__logo-icon">✦</span>
                <span class="navbar__logo-text">
                    <span class="navbar__logo-main">Afro Elegance</span>
                    <span class="navbar__logo-sub">Custom Clothing</span>
                </span>
            </a>

            <!-- Mobile Menu Toggle -->
            <button class="navbar__toggle" aria-label="Toggle navigation menu" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Navigation Links -->
            <ul class="navbar__menu" id="navMenu">
                <li><a href="/products" class="navbar__link">Home</a></li>
                <li><a href="/appointments" class="navbar__link">Appointments</a></li>
                <li><a href="/about" class="navbar__link">About</a></li>
                <li><a href="/contact" class="navbar__link">Contact</a></li>
            </ul>

            <!-- User Actions -->
            <div class="navbar__actions">
                <?php if (isset($_SESSION['userId'])): ?>
                    <!-- Logged In User -->
                    <a href="/favourites" class="navbar__cart">

                        <span>Favourites</span>
                    </a>
                    <a href="/viewCart" class="navbar__cart" aria-label="Shopping cart">
                        🛒 Cart
                    </a>
                    <div class="navbar__user">
                        <button class="navbar__user-btn" id="userMenuBtn">
                            <span class="navbar__user-avatar">
                                <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                            </span>
                            <span class="navbar__user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                        </button>
                        <div class="navbar__dropdown" id="userDropdown">
                            <a href="/profile" class="navbar__dropdown-item">My Profile</a>
                            <a href="/orders" class="navbar__dropdown-item">My Orders</a>
                            <a href="/my-appointments" class="navbar__dropdown-item">My Appointments</a>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <hr class="navbar__dropdown-divider">
                                <a href="/admin" class="navbar__dropdown-item navbar__dropdown-item--admin">Admin Panel</a>
                            <?php endif; ?>
                            <hr class="navbar__dropdown-divider">
                            <a href="/logout" class="navbar__dropdown-item navbar__dropdown-item--logout">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Guest User -->
                    <a href="/showLogin" class="btn btn--ghost navbar__btn">Login</a>
                    <a href="/showRegistrationForm" class="btn btn--primary navbar__btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ============================================
         MAIN CONTENT AREA
         ============================================ -->
    <main class="main-content">
        <?php require $content; ?>
    </main>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="footer">
        <div class="footer__container">
            <!-- Brand Column -->
            <div class="footer__column footer__column--brand">
                <div class="footer__logo">
                    <span class="footer__logo-icon">✦</span>
                    <span class="footer__logo-text">Afro Elegance</span>
                </div>
                <p class="footer__description">
                    Celebrating African beauty through custom-designed clothing.
                    Each piece tells a story, woven with tradition and modern elegance.
                </p>
                <div class="footer__social">
                    <a href="#" class="footer__social-link" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="Twitter">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path
                                d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                        </svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="Pinterest">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path
                                d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer__column">
                <h3 class="footer__title">Quick Links</h3>
                <ul class="footer__links">
                    <li><a href="/" class="footer__link">Home</a></li>
                    <li><a href="/products" class="footer__link">Shop Collection</a></li>
                    <li><a href="/appointments" class="footer__link">Book Appointment</a></li>
                    <li><a href="/about" class="footer__link">About Us</a></li>
                    <li><a href="/contact" class="footer__link">Contact</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer__column">
                <h3 class="footer__title">Customer Care</h3>
                <ul class="footer__links">
                    <li><a href="/orders" class="footer__link">Track Order</a></li>
                    <li><a href="/faq" class="footer__link">FAQs</a></li>
                    <li><a href="/shipping" class="footer__link">Shipping Info</a></li>
                    <li><a href="/returns" class="footer__link">Returns & Exchanges</a></li>
                    <li><a href="/size-guide" class="footer__link">Size Guide</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer__column">
                <h3 class="footer__title">Get In Touch</h3>
                <ul class="footer__contact">
                    <li class="footer__contact-item">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                            <path
                                d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z" />
                        </svg>
                        <span>+31 (0) 70 123 4567</span>
                    </li>
                    <li class="footer__contact-item">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                            <path
                                d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                        </svg>
                        <span>hello@afroelegance.com</span>
                    </li>
                    <li class="footer__contact-item">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                            <path
                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                        </svg>
                        <span>The Hague, Netherlands</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="footer__bottom">
            <div class="footer__bottom-container">
                <p class="footer__copyright">
                    © <?= date('Y') ?> Afro Elegance. Crafted with ❤️ by Amazinggrace Iruoma
                </p>
                <div class="footer__legal">
                    <a href="/privacy" class="footer__legal-link">Privacy Policy</a>
                    <span class="footer__legal-separator">•</span>
                    <a href="/terms" class="footer__legal-link">Terms of Service</a>
                    <span class="footer__legal-separator">•</span>
                    <a href="/cookies" class="footer__legal-link">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="/assets/js/csrf-fetch.js"></script>
    <script src="/assets/js/navbar.js"></script>
</body>

</html>