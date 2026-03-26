<?php
declare(strict_types=1);

$settingsState = is_array($settingsState ?? null) ? $settingsState : [];
$settingsItems = is_array($settingsItems ?? null) ? $settingsItems : [];
?>
<section class="admin-dashboard admin-dashboard--settings">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Settings Management</p>
            <h1>Global Settings</h1>
            <p class="admin-dashboard__intro">Manage footer and shared global content that appears across the public temple website.</p>
        </div>
    </header>

    <?php if (($settingsState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($settingsState['type'] ?? 'info') ?>">
            <?= e($settingsState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="admin-panel admin-panel--settings-list">
        <div class="admin-panel__header admin-panel__header--inline">
            <div>
                <h2>Settings Library</h2>
                <p class="admin-events-panel__subcopy">Open a global settings editor and update shared public content without touching code.</p>
            </div>
        </div>

        <div class="admin-settings-table">
            <?php foreach ($settingsItems as $item): ?>
                <article class="admin-settings-row">
                    <div class="admin-settings-row__main">
                        <h3><?= e($item['title'] ?? '') ?></h3>
                        <p><?= e($item['description'] ?? '') ?></p>
                    </div>
                    <div class="admin-settings-row__meta">
                        <span><?= e((string) ($item['fields_count'] ?? 0)) ?> fields</span>
                        <small><?= e($item['updated_label'] ?? 'Settings pending') ?></small>
                    </div>
                    <div class="admin-settings-row__status">
                        <span class="admin-events-phase admin-events-phase--active">Ready</span>
                    </div>
                    <div class="admin-settings-row__actions">
                        <a href="<?= e($item['href'] ?? route_url('/admin/settings')) ?>">Edit</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>
