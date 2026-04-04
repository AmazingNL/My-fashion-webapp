<main class="auth__card">
    <header class="auth__header">
        <h2>Register</h2>
        <p>Fill in your details to get started.</p>
    </header>

    <form id="registerForm" class="form" action="/registerUser" method="post" novalidate>
        <?= $this->csrfField(); ?>

        <?php if (!empty($errors)): ?>
            <div id="formErrors" class="notice notice--error">
                <ul>
                    <?php foreach ((array) $errors as $error): ?>
                        <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div id="formErrors" class="notice notice--error" hidden></div>
        <?php endif; ?>

        <div class="form__grid">
            <div class="field">
                <input id="firstName" name="firstName" placeholder=" " autocomplete="given-name" required value="<?= htmlspecialchars((string) ($oldInput['firstName'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                <label for="firstName">First name</label>
            </div>

            <div class="field">
                <input id="lastName" name="lastName" placeholder=" " autocomplete="family-name" required value="<?= htmlspecialchars((string) ($oldInput['lastName'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                <label for="lastName">Last name</label>
            </div>

            <div class="field field--full">
                <input id="email" name="email" type="email" placeholder=" " autocomplete="email" required value="<?= htmlspecialchars((string) ($oldInput['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                <label for="email">Email</label>
            </div>

            <div class="field field--full">
                <input id="phone" name="phone" placeholder=" " autocomplete="tel" required value="<?= htmlspecialchars((string) ($oldInput['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
                <label for="phone">Phone</label>
            </div>

            <div class="field field--full">
                <input id="password" name="password" type="password" placeholder=" " autocomplete="new-password" required />
                <label for="password">Password</label>
            </div>
        </div>

        <button class="btn" type="submit">
            <span>Create account</span>
            <span class="btn__spark" aria-hidden="true"></span>
        </button>

        <p class="auth__hint">
            Already have an account? <a href="/">Sign in</a>
        </p>
    </form>
</main>
