<section class="af-card" aria-labelledby="fpTitle">
    <header>
        <h2 class="af-h2" id="fpTitle">Reset password</h2>
        <p class="af-p">We will send a code + link to confirm the change.</p>
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

    <form class="af-form" action="/forgotPassword" method="post" novalidate>
        <?= $this->csrfField(); ?>

        <div class="af-grid">
            <div class="af-field af-full">
                <input class="af-input" id="email" name="email" type="email" placeholder=" "
                    autocomplete="email" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                <label class="af-label" for="email">Email</label>
            </div>

            <div class="af-field af-full">
                <input class="af-input" id="newPassword" name="newPassword" type="password" placeholder=" " required>
                <label class="af-label" for="newPassword">New password</label>
            </div>

            <div class="af-field af-full">
                <input class="af-input" id="confirmPassword" name="confirmPassword" type="password" placeholder=" "
                    required>
                <label class="af-label" for="confirmPassword">Confirm password</label>
            </div>
        </div>

        <button class="af-btn" type="submit">
            <span class="af-dot" aria-hidden="true"></span>
            Send code
        </button>

        <p class="af-mini">
            Back to <a class="af-link" href="/">Login</a>
        </p>
    </form>
</section>
