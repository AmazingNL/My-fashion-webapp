<main class="af-shell">
    <section class="af-wrap login-layout">

        <aside class="af-card login-brand">
            <span class="af-badge">Afro Fashion</span>
            <h1 class="af-title">Verify code ✉️</h1>
            <p class="af-subtitle">Check your email for the 6-digit code.</p>
        </aside>

        <section class="af-card" aria-labelledby="rcTitle">
            <header>
                <h2 class="af-h2" id="rcTitle">Enter the code</h2>
                <p class="af-p">Once verified, your password will be updated and you’ll go to products.</p>
            </header>

            <div id="formErrors" class="af-alert af-alert--error" role="alert" aria-live="polite" hidden></div>

            <form id="codeForm" class="af-form" action="/reset-password/verify" method="post" novalidate>
                <?= $this->csrfField(); ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <div class="af-grid">
                    <div class="af-field af-full">
                        <input class="af-input" id="code" name="code" inputmode="numeric" maxlength="6" placeholder=" "
                            required>
                        <label class="af-label" for="code">6-digit code</label>
                    </div>
                </div>

                <button class="af-btn" id="verifyBtn" type="submit">
                    <span class="af-dot" aria-hidden="true"></span>
                    Verify
                </button>

                <p class="af-mini">
                    Wrong email? <a class="af-link" href="/forgotPassword">Start again</a>
                </p>
            </form>
        </section>

    </section>
</main>