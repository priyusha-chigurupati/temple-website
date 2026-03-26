<?php
declare(strict_types=1);

$authState = is_array($authState ?? null) ? $authState : [];
$authForm = is_array($authForm ?? null) ? $authForm : [];
$resetLink = is_string($resetLink ?? null) ? $resetLink : '';
?>
<section class="admin-login admin-login--supporting">
    <div class="admin-login__brand">
        <h1>AnkammaThalli Temple</h1>
        <p>Administrative Gateway</p>
        <div class="admin-login__divider"></div>
    </div>

    <div class="admin-login__panel">
        <div class="admin-login__panel-accent" aria-hidden="true"></div>

        <header class="admin-login__header">
            <h2>Forgot Password</h2>
            <p>Enter the admin email address. For now, local development will show the reset link directly until SMTP is connected.</p>
        </header>

        <?php if (! empty($authState['message'])): ?>
            <p class="admin-status admin-status--<?= e($authState['type'] ?? 'info') ?>"><?= e($authState['message']) ?></p>
        <?php endif; ?>

        <?php if ($resetLink !== ''): ?>
            <div class="admin-form-state admin-form-state--success">
                <strong>Local reset link:</strong>
                <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
            </div>
        <?php endif; ?>

        <form class="admin-form" action="<?= e(route_url('/admin/forgot-password')) ?>" method="post">
            <?= csrf_input() ?>
            <label class="admin-form__field">
                <span>Admin Email Address</span>
                <div class="admin-form__control">
                    <span class="material-symbols-outlined">mail</span>
                    <input type="email" name="email" value="<?= e((string) ($authForm['email'] ?? '')) ?>" placeholder="admin@ankammathalli.local" required>
                </div>
            </label>

            <button class="button button--gradient button--full admin-login__submit" type="submit">Create Reset Request</button>
        </form>

        <div class="admin-login__meta admin-login__meta--centered">
            <a class="admin-login__hint-link" href="<?= e(route_url('/admin/login')) ?>">Back to Login</a>
        </div>
    </div>
</section>
