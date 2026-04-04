<section class="af-card" aria-labelledby="loginTitle">
    <header>
        <h2 class="af-h2" id="loginTitle">Log in</h2>
        <p class="af-p">Enter your details to continue.</p>
    </header>

    <?php if ($error !== ''): ?>
        <div class="af-alert af-alert--error" role="alert" aria-live="polite">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="af-alert af-alert--success" role="status" aria-live="polite">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form class="af-form" action="/login" method="post" novalidate>
        <?= $this->csrfField(); ?>
        <div class="af-grid">

            <div class="af-field af-full">
                <input class="af-input" id="email" name="email" type="email" placeholder=" "
                    autocomplete="email" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                <label class="af-label" for="email">Email</label>
            </div>

            <div class="af-field af-full">
                <input class="af-input" id="password" name="password" type="password" placeholder=" "
                    autocomplete="current-password" required>
                <label class="af-label" for="password">Password</label>
            </div>

        </div>

        <div class="af-row">
            <span></span>
            <a class="af-link" href="/forgotPassword">Forgot password?</a>
        </div>

        <button class="af-btn" type="submit">
            <span class="af-dot" aria-hidden="true"></span>
            Log in
        </button>

        <p class="af-mini">
            New here? <a class="af-link" href="/showRegistrationForm">Create account</a>
        </p>

        <p class="af-mini">
            By logging in, you agree to our <a class="af-link" href="/aboutUs">Terms</a>.
        </p>
    </form>
</section>
