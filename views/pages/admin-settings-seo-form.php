<?php
declare(strict_types=1);

$settingsState = is_array($settingsState ?? null) ? $settingsState : [];
$seoForm = is_array($seoForm ?? null) ? $seoForm : [];
?>
<section class="admin-dashboard admin-dashboard--settings-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Settings / SEO</p>
            <h1>Edit SEO Settings</h1>
            <p class="admin-dashboard__intro">Manage the site-wide title suffix and default meta description used when a page does not define its own SEO copy.</p>
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

    <form class="admin-events-form" action="<?= e(route_url('/admin/settings/seo')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Global SEO Defaults</h2>
                        <p class="admin-events-panel__subcopy">These values are used as fallbacks across the public website when a page does not define its own metadata.</p>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="title-suffix">Title Suffix</label>
                    <input id="title-suffix" name="title_suffix" type="text" value="<?= e((string) ($seoForm['title_suffix'] ?? '')) ?>" required>
                </div>
                <div class="admin-field">
                    <label for="default-description">Default Meta Description</label>
                    <textarea id="default-description" name="default_description" rows="4" required><?= e((string) ($seoForm['default_description'] ?? '')) ?></textarea>
                </div>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/settings')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save SEO Settings</button>
        </div>
    </form>
</section>
