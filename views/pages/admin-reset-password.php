<?php
declare(strict_types=1);

$authState = is_array($authState ?? null) ? $authState : [];
$resetForm = is_array($resetForm ?? null) ? $resetForm : [];
$resetContext = is_array($resetContext ?? null) ? $resetContext : null;
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
            <h2>Reset Password</h2>
            <p>Create a new password for the admin dashboard using the secure reset link.</p>
        </header>

        <?php if (! empty($authState['message'])): ?>
            <p class="admin-status admin-status--<?= e($authState['type'] ?? 'info') ?>"><?= e($authState['message']) ?></p>
        <?php endif; ?>

        <?php if ($resetContext === null): ?>
            <div class="admin-form-state admin-form-state--error">
                This reset link is invalid or has expired. Please request a new password reset.
            </div>
            <div class="admin-login__meta admin-login__meta--centered">
                <a class="admin-login__hint-link" href="<?= e(route_url('/admin/forgot-password')) ?>">Request a new reset link</a>
            </div>
        <?php else: ?>
            <div class="admin-form-state admin-form-state--success">
                Resetting password for <?= e((string) ($resetContext['email'] ?? '')) ?>
            </div>

            <form class="admin-form" action="<?= e(route_url('/admin/reset-password?token=' . rawurlencode((string) $resetToken))) ?>" method="post">
                <?= csrf_input() ?>
                <input type="hidden" name="token" value="<?= e((string) $resetToken) ?>">

                <label class="admin-form__field">
                    <span>New Password</span>
                    <div class="admin-form__control">
                        <span class="material-symbols-outlined">lock</span>
                        <input type="password" name="new_password" placeholder="Enter a new password" autocomplete="new-password" required>
                    </div>
                </label>

                <label class="admin-form__field">
                    <span>Confirm New Password</span>
                    <div class="admin-form__control">
                        <span class="material-symbols-outlined">lock_reset</span>
                        <input type="password" name="confirm_password" placeholder="Confirm the new password" autocomplete="new-password" required>
                    </div>
                </label>

                <button class="button button--gradient button--full admin-login__submit" type="submit">Save New Password</button>
            </form>
        <?php endif; ?>
    </div>
</section>
