<?php
declare(strict_types=1);

$accountState = is_array($accountState ?? null) ? $accountState : [];
$accountForm = is_array($accountForm ?? null) ? $accountForm : [];
?>
<section class="admin-dashboard admin-dashboard--settings-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Account / Profile</p>
            <h1>Admin Account</h1>
            <p class="admin-dashboard__intro">Update the main admin identity used across the dashboard and password reset flow.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/account/password')) ?>">Change Password</a>
        </div>
    </header>

    <?php if (($accountState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($accountState['type'] ?? 'info') ?>">
            <?= e($accountState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e(route_url('/admin/account')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Profile Details</h2>
                        <p class="admin-events-panel__subcopy">This controls the admin name and email shown in the dashboard sidebar and used for future password reset requests.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="admin-name">Admin Name</label>
                        <input id="admin-name" name="name" type="text" value="<?= e((string) ($accountForm['name'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="admin-email">Admin Email</label>
                        <input id="admin-email" name="email" type="email" value="<?= e((string) ($accountForm['email'] ?? '')) ?>" required>
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Security Notes</h2>
                        <p class="admin-events-panel__subcopy">Keep this email active. It will be the identity used for password reset and future admin notification workflows.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="session-timeout">Session Timeout</label>
                        <input id="session-timeout" type="text" value="30 minutes of inactivity" disabled>
                        <small>This is currently managed through the server environment setting.</small>
                    </div>
                    <div class="admin-field">
                        <label for="reset-mode">Reset Delivery</label>
                        <input id="reset-mode" type="text" value="Local link now, SMTP later" disabled>
                        <small>Real email delivery can be connected later from Notification Settings.</small>
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin')) ?>">Back to Dashboard</a>
            <button class="admin-primary-action" type="submit">Save Account Details</button>
        </div>
    </form>
</section>
