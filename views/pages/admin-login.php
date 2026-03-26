<?php
declare(strict_types=1);
?>
<section class="admin-login">
    <div class="admin-login__panel">
        <p class="admin-login__eyebrow">Temple Administration</p>
        <h1>Admin Login</h1>
        <p class="admin-login__copy">Use your administrator credentials to manage the temple website content and submissions.</p>

        <?php if (! empty($authState['message'])): ?>
            <p class="admin-status admin-status--<?= e($authState['type'] ?? 'info') ?>"><?= e($authState['message']) ?></p>
        <?php endif; ?>

        <form class="admin-form" action="<?= e(route_url('/admin/login')) ?>" method="post">
            <?= csrf_input() ?>
            <label class="admin-form__field">
                <span>Email Address</span>
                <input type="email" name="email" value="<?= e($authForm['email'] ?? '') ?>" placeholder="admin@ankammathalli.local" required>
            </label>

            <label class="admin-form__field">
                <span>Password</span>
                <input type="password" name="password" placeholder="Enter your password" required>
            </label>

            <button class="button button--gradient button--full" type="submit">Log In</button>
        </form>
    </div>
</section>
