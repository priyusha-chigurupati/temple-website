<?php
declare(strict_types=1);

$settingsState = is_array($settingsState ?? null) ? $settingsState : [];
$generalForm = is_array($generalForm ?? null) ? $generalForm : [];
?>
<section class="admin-dashboard admin-dashboard--settings-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Settings / General</p>
            <h1>Edit General Settings</h1>
            <p class="admin-dashboard__intro">Manage core site identity, locale preferences, and shared contact references for the temple website.</p>
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

    <form class="admin-events-form" action="<?= e(route_url('/admin/settings/general')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Site Identity</h2>
                        <p class="admin-events-panel__subcopy">Primary site name and tagline used across the temple website.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="site-name">Site Name</label>
                        <input id="site-name" name="site_name" type="text" value="<?= e((string) ($generalForm['site_name'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="tagline">Tagline</label>
                        <input id="tagline" name="tagline" type="text" value="<?= e((string) ($generalForm['tagline'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Contact References</h2>
                        <p class="admin-events-panel__subcopy">Shared site-level contact values that can be reused by public pages and future integrations.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="contact-phone">Phone Number</label>
                        <input id="contact-phone" name="contact_phone" type="text" value="<?= e((string) ($generalForm['contact_phone'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="contact-email">Email Address</label>
                        <input id="contact-email" name="contact_email" type="email" value="<?= e((string) ($generalForm['contact_email'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="contact-address">Shared Address</label>
                    <textarea id="contact-address" name="contact_address" rows="3"><?= e((string) ($generalForm['contact_address'] ?? '')) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="map-url">Map URL</label>
                    <input id="map-url" name="map_url" type="url" value="<?= e((string) ($generalForm['map_url'] ?? '')) ?>">
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Language Defaults</h2>
                        <p class="admin-events-panel__subcopy">Store the default locale and supported locale codes so the site is ready for English and Telugu content management.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="default-locale">Default Locale</label>
                        <input id="default-locale" name="default_locale" type="text" value="<?= e((string) ($generalForm['default_locale'] ?? 'en')) ?>" required>
                        <small>Example: <code>en</code></small>
                    </div>
                    <div class="admin-field">
                        <label for="supported-locales">Supported Locales</label>
                        <input id="supported-locales" name="supported_locales" type="text" value="<?= e((string) ($generalForm['supported_locales'] ?? 'en,te')) ?>" required>
                        <small>Comma-separated locale codes, for example: <code>en,te</code></small>
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/settings')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save General Settings</button>
        </div>
    </form>
</section>
