<?php
declare(strict_types=1);

$settingsState = is_array($settingsState ?? null) ? $settingsState : [];
$notificationsForm = is_array($notificationsForm ?? null) ? $notificationsForm : [];
?>
<section class="admin-dashboard admin-dashboard--settings-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Settings / Notifications</p>
            <h1>Edit Notification Settings</h1>
            <p class="admin-dashboard__intro">Store the future mail sender details, SMTP values, and alert recipients for contact, donation, and gallery workflows.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/settings')) ?>">Back to Settings</a>
        </div>
    </header>

    <?php if (($settingsState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($settingsState['type'] ?? 'info') ?>">
            <?= e($settingsState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e(route_url('/admin/settings/notifications')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Delivery Mode</h2>
                        <p class="admin-events-panel__subcopy">Keep this on manual until you have the real mail credentials. Later it can be switched to SMTP for Zoho or another provider.</p>
                    </div>
                </div>
                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="delivery-mode">Mail Delivery Mode</label>
                        <select id="delivery-mode" name="delivery_mode">
                            <option value="manual"<?= (($notificationsForm['delivery_mode'] ?? 'manual') === 'manual') ? ' selected' : '' ?>>Manual / Local</option>
                            <option value="smtp"<?= (($notificationsForm['delivery_mode'] ?? '') === 'smtp') ? ' selected' : '' ?>>SMTP</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="from-name">Sender Name</label>
                        <input id="from-name" name="from_name" type="text" value="<?= e((string) ($notificationsForm['from_name'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="from-email">Sender Email</label>
                        <input id="from-email" name="from_email" type="email" value="<?= e((string) ($notificationsForm['from_email'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="reply-to-email">Reply-To Email</label>
                    <input id="reply-to-email" name="reply_to_email" type="email" value="<?= e((string) ($notificationsForm['reply_to_email'] ?? '')) ?>">
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>SMTP Connection</h2>
                        <p class="admin-events-panel__subcopy">These are the values you will later collect from Zoho or another mail provider. You can leave them blank for now.</p>
                    </div>
                </div>
                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="smtp-host">SMTP Host</label>
                        <input id="smtp-host" name="smtp_host" type="text" value="<?= e((string) ($notificationsForm['smtp_host'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="smtp-port">SMTP Port</label>
                        <input id="smtp-port" name="smtp_port" type="text" value="<?= e((string) ($notificationsForm['smtp_port'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="smtp-encryption">Encryption</label>
                        <select id="smtp-encryption" name="smtp_encryption">
                            <option value="tls"<?= (($notificationsForm['smtp_encryption'] ?? 'tls') === 'tls') ? ' selected' : '' ?>>TLS</option>
                            <option value="ssl"<?= (($notificationsForm['smtp_encryption'] ?? '') === 'ssl') ? ' selected' : '' ?>>SSL</option>
                            <option value="none"<?= (($notificationsForm['smtp_encryption'] ?? '') === 'none') ? ' selected' : '' ?>>None</option>
                        </select>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="smtp-username">SMTP Username</label>
                        <input id="smtp-username" name="smtp_username" type="text" value="<?= e((string) ($notificationsForm['smtp_username'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="smtp-password">SMTP Password / App Password</label>
                        <input id="smtp-password" name="smtp_password" type="text" value="<?= e((string) ($notificationsForm['smtp_password'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Alert Recipients</h2>
                        <p class="admin-events-panel__subcopy">Choose which email should later receive admin alerts from public forms and moderation workflows.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="admin-alert-email">Admin Alert Email</label>
                        <input id="admin-alert-email" name="admin_alert_email" type="email" value="<?= e((string) ($notificationsForm['admin_alert_email'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="contact-alert-email">Contact Inquiry Alerts</label>
                        <input id="contact-alert-email" name="contact_alert_email" type="email" value="<?= e((string) ($notificationsForm['contact_alert_email'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="donation-alert-email">Donation Notice Alerts</label>
                        <input id="donation-alert-email" name="donation_alert_email" type="email" value="<?= e((string) ($notificationsForm['donation_alert_email'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="gallery-alert-email">Gallery Submission Alerts</label>
                        <input id="gallery-alert-email" name="gallery_alert_email" type="email" value="<?= e((string) ($notificationsForm['gallery_alert_email'] ?? '')) ?>">
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/settings')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save Notification Settings</button>
        </div>
    </form>
</section>
