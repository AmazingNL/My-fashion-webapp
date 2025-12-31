<?php
// views/users/showRegistrationForm.php
?>


<link rel="stylesheet" href="/assets/css/register.css">

<section class="auth">
    <div class="auth__wrap">
        <aside class="auth__brand">
            <div class="brand__badge">NuellaSignet</div>
            <h1>Create your account</h1>
            <p>
                Join our boutique and shop Afro-inspired women’s fashion. Save favorites, track orders, and get new
                drops.
            </p>

            <div class="brand__tiles">
                <div class="tile">✨ New arrivals</div>
                <div class="tile">🧵 Bold prints</div>
                <div class="tile">🛍️ Easy checkout</div>
            </div>
        </aside>

        <main class="auth__card">
            <header class="auth__header">
                <h2>Register</h2>
                <p>Fill in your details to get started.</p>
            </header>

            <form id="registerForm" class="form" action="/registerUser" method="post" novalidate>
                <?= $this->csrfField(); ?>

                <div class="form__grid">
                    <div class="field">
                        <input id="firstName" name="firstName" placeholder=" " autocomplete="given-name" required />
                        <label for="firstName">First name</label>
                    </div>

                    <div class="field">
                        <input id="lastName" name="lastName" placeholder=" " autocomplete="family-name" required />
                        <label for="lastName">Last name</label>
                    </div>

                    <div class="field field--full">
                        <input id="email" name="email" type="email" placeholder=" " autocomplete="email" required />
                        <label for="email">Email</label>
                    </div>

                    <div class="field field--full">
                        <input id="phone" name="phone" placeholder=" " autocomplete="tel" required />
                        <label for="phone">Phone</label>
                    </div>

                    <div class="field field--full">
                        <input id="password" name="password" type="password" placeholder=" " autocomplete="new-password"
                            required />
                        <label for="password">Password</label>

                        <button class="field__toggle" type="button" data-toggle="password"
                            aria-label="Toggle password visibility">
                            Show
                        </button>
                    </div>
                </div>

                <button class="btn" type="submit">
                    <span>Create account</span>
                    <span class="btn__spark" aria-hidden="true"></span>
                </button>

                <p class="auth__hint">
                    Already have an account? <a href="/login">Sign in</a>
                </p>
            </form>

            <div id="formErrors" class="notice notice--error" hidden></div>
            <div id="formSuccess" class="notice notice--success" hidden></div>
        </main>
    </div>
</section>

<script src="/assets/js/register.js" defer></script>