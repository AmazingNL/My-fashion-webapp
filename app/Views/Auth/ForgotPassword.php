<main class="af-shell">
    <section class="af-wrap login-layout">

        <aside class="af-card login-brand">
            <span class="af-badge">Afro Fashion</span>
            <h1 class="af-title">Forgot password 🔐</h1>
            <p class="af-subtitle">Enter your email and new password. We’ll email a verification code.</p>
        </aside>

        <section class="af-card" aria-labelledby="fpTitle">
            <header>
                <h2 class="af-h2" id="fpTitle">Reset password</h2>
                <p class="af-p">We’ll send a code + link to confirm the change.</p>
            </header>

            <div id="formErrors" class="af-alert af-alert--error" role="alert" aria-live="polite" hidden></div>

            <form id="forgotForm" class="af-form" action="/forgotPassword" method="post" novalidate>
                <?= $this->csrfField(); ?>

                <div class="af-grid">
                    <div class="af-field af-full">
                        <input class="af-input" id="email" name="email" type="email" placeholder=" "
                            autocomplete="email" required>
                        <label class="af-label" for="email">Email</label>
                    </div>

                    <div class="af-field af-full">
                        <input class="af-input" id="newPassword" name="newPassword" type="password" placeholder=" "
                            required>
                        <label class="af-label" for="newPassword">New password</label>
                    </div>

                    <div class="af-field af-full">
                        <input class="af-input" id="confirmPassword" name="confirmPassword" type="password"
                            placeholder=" " required>
                        <label class="af-label" for="confirmPassword">Confirm password</label>
                    </div>
                </div>

                <button class="af-btn" id="sendBtn" type="submit">
                    <span class="af-dot" aria-hidden="true"></span>
                    Send code
                </button>

                <p class="af-mini">
                    Back to <a class="af-link" href="/">Login</a>
                </p>
            </form>
        </section>

    </section>
</main>