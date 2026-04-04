<section class="af-card" aria-labelledby="rcTitle">
    <header>
        <h2 class="af-h2" id="rcTitle">Enter the code</h2>
        <p class="af-p">Once verified, your password will be updated and you will go to login.</p>
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

    <form class="af-form" action="/reset-password/verify" method="post" novalidate>
        <?= $this->csrfField(); ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="af-grid">
            <div class="af-field af-full">
                <input class="af-input" id="code" name="code" inputmode="numeric" maxlength="6" placeholder=" "
                    required>
                <label class="af-label" for="code">6-digit code</label>
            </div>
        </div>

        <button class="af-btn" type="submit">
            <span class="af-dot" aria-hidden="true"></span>
            Verify
        </button>

        <p class="af-mini">
            Wrong email? <a class="af-link" href="/forgotPassword">Start again</a>
        </p>
    </form>
</section>
