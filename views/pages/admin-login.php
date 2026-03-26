<?php
declare(strict_types=1);
?>
<section class="admin-login">
    <div class="admin-login__brand">
        <h1>AnkammaThalli Temple</h1>
        <p>Administrative Gateway</p>
        <div class="admin-login__divider"></div>
    </div>

    <div class="admin-login__panel">
        <div class="admin-login__panel-accent" aria-hidden="true"></div>

        <header class="admin-login__header">
            <h2>Secure Login</h2>
            <p>Please enter your credentials to access the temple management system.</p>
        </header>

        <?php if (! empty($authState['message'])): ?>
            <p class="admin-status admin-status--<?= e($authState['type'] ?? 'info') ?>"><?= e($authState['message']) ?></p>
        <?php endif; ?>

        <form class="admin-form" action="<?= e(route_url('/admin/login')) ?>" method="post">
            <?= csrf_input() ?>
            <label class="admin-form__field">
                <span>Email Address</span>
                <div class="admin-form__control">
                    <span class="material-symbols-outlined">person</span>
                    <input type="email" name="email" value="<?= e($authForm['email'] ?? '') ?>" placeholder="e.g. admin@ankammathalli.local" required>
                </div>
            </label>

            <label class="admin-form__field">
                <span>Password</span>
                <div class="admin-form__control">
                    <span class="material-symbols-outlined">lock</span>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
            </label>

            <div class="admin-login__meta">
                <label class="admin-login__remember">
                    <input type="checkbox" disabled>
                    <span>Remember me</span>
                </label>
                <span class="admin-login__hint">Password reset comes next.</span>
            </div>

            <button class="button button--gradient button--full admin-login__submit" type="submit">Sign In to Dashboard</button>
        </form>

        <div class="admin-login__security">
            <span class="material-symbols-outlined">security</span>
            <span>Encrypted Session</span>
        </div>
    </div>

    <div class="admin-login__return">
        <a href="<?= e(route_url('/')) ?>">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Return to Main Website</span>
        </a>
    </div>
</section>
