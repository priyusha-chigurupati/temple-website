<?php
declare(strict_types=1);

$settingsState = is_array($settingsState ?? null) ? $settingsState : [];
$headerForm = is_array($headerForm ?? null) ? $headerForm : [];
$navigationItems = is_array($headerForm['navigation_items'] ?? null) ? $headerForm['navigation_items'] : [];
?>
<section class="admin-dashboard admin-dashboard--settings-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Settings / Header</p>
            <h1>Edit Header Settings</h1>
            <p class="admin-dashboard__intro">Manage the public header donate button and primary navigation labels and links.</p>
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

    <form class="admin-events-form" action="<?= e(route_url('/admin/settings/header')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Primary Header Button</h2>
                        <p class="admin-events-panel__subcopy">Update the label and href used by the main header call-to-action.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="primary-cta-label">Button Label</label>
                        <input id="primary-cta-label" name="primary_cta_label" type="text" value="<?= e((string) ($headerForm['primary_cta_label'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="primary-cta-href">Button Href</label>
                        <input id="primary-cta-href" name="primary_cta_href" type="text" value="<?= e((string) ($headerForm['primary_cta_href'] ?? '')) ?>" required>
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Navigation Items</h2>
                        <p class="admin-events-panel__subcopy">Update the public navigation order by editing the labels and hrefs below.</p>
                    </div>
                </div>
                <?php foreach ($navigationItems as $index => $item): ?>
                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="navigation-item-<?= e((string) ($index + 1)) ?>-label">Item <?= e((string) ($index + 1)) ?> Label</label>
                            <input id="navigation-item-<?= e((string) ($index + 1)) ?>-label" name="navigation_item_<?= e((string) ($index + 1)) ?>_label" type="text" value="<?= e((string) ($item['label'] ?? '')) ?>" required>
                        </div>
                        <div class="admin-field">
                            <label for="navigation-item-<?= e((string) ($index + 1)) ?>-href">Item <?= e((string) ($index + 1)) ?> Href</label>
                            <input id="navigation-item-<?= e((string) ($index + 1)) ?>-href" name="navigation_item_<?= e((string) ($index + 1)) ?>_href" type="text" value="<?= e((string) ($item['href'] ?? '')) ?>" required>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/settings')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save Header Settings</button>
        </div>
    </form>
</section>
