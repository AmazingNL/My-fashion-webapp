<?php
// views/users/login.php
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login</title>


</head>

<body>
    <main class="af-shell">
        <section class="af-wrap login-layout">

            <!-- Left -->
            <aside class="af-card login-brand">
                <span class="af-badge">Afro Fashion</span>
                <h1 class="af-title">Welcome back ✨</h1>
                <p class="af-subtitle">Log in to continue shopping. Your style is waiting.</p>

                <div class="login-tiles">
                    <div class="login-tile">✨ New drops every week</div>
                    <div class="login-tile">🟣 Afro-inspired modern looks</div>
                    <div class="login-tile">🧡 Easy checkout + favourites</div>
                </div>
            </aside>

            <!-- Right -->
            <section class="af-card" aria-labelledby="loginTitle">
                <header>
                    <h2 class="af-h2" id="loginTitle">Log in</h2>
                    <p class="af-p">Enter your details to continue.</p>
                </header>

                <div id="formErrors" class="af-alert af-alert--error" role="alert" aria-live="polite" hidden></div>
                <div id="formSuccess" class="af-alert af-alert--success" role="status" aria-live="polite" hidden></div>

                <form id="loginForm" class="af-form" action="/login" method="post" novalidate>
                    <?= $this->csrfField(); ?>
                    <div class="af-grid">

                        <div class="af-field af-full">
                            <input class="af-input" id="email" name="email" type="email" placeholder=" "
                                autocomplete="email" required>
                            <label class="af-label" for="email">Email</label>
                        </div>

                        <div class="af-field af-full">
                            <input class="af-input" id="password" name="password" type="password" placeholder=" "
                                autocomplete="current-password" required>
                            <label class="af-label" for="password">Password</label>

                            <button class="af-toggle" type="button" data-toggle="password"
                                aria-label="Show password">👁️</button>
                        </div>

                    </div>

                    <div class="af-row">
                        <span></span>
                        <a class="af-link" href="/forgotPassword">Forgot password?</a>
                    </div>

                    <button class="af-btn" id="loginBtn" type="submit">
                        <span class="af-dot" aria-hidden="true"></span>
                        Log in
                    </button>

                    <p class="af-mini">
                        New here? <a class="af-link" href="/register">Create account</a>
                    </p>

                    <p class="af-mini">
                        By logging in, you agree to our <a class="af-link" href="/terms">Terms</a>.
                    </p>
                </form>
            </section>

        </section>
    </main>

    <script src="/assets/js/login.js" defer></script>
</body>

</html>