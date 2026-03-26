<?php
declare(strict_types=1);

$accountPasswordState = is_array($accountPasswordState ?? null) ? $accountPasswordState : [];
?>
<section class="admin-dashboard admin-dashboard--settings-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Account / Security</p>
            <h1>Change Password</h1>
            <p class="admin-dashboard__intro">Update the admin password with the current-password confirmation flow expected in a standard dashboard.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/account')) ?>">Back to Account</a>
        </div>
    </header>

    <?php if (($accountPasswordState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($accountPasswordState['type'] ?? 'info') ?>">
            <?= e($accountPasswordState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e(route_url('/admin/account/password')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Password Update</h2>
                        <p class="admin-events-panel__subcopy">Use a strong password with at least 10 characters. This form checks the current password before saving the new one.</p>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="current-password">Current Password</label>
                    <input id="current-password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="new-password">New Password</label>
                        <input id="new-password" name="new_password" type="password" autocomplete="new-password" required>
                    </div>
                    <div class="admin-field">
                        <label for="confirm-password">Confirm New Password</label>
                        <input id="confirm-password" name="confirm_password" type="password" autocomplete="new-password" required>
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/account')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Update Password</button>
        </div>
    </form>
</section>
