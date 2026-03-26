<?php
declare(strict_types=1);

$pagesState = is_array($pagesState ?? null) ? $pagesState : [];
$pagesList = is_array($pagesList ?? null) ? $pagesList : [];
?>
<section class="admin-dashboard admin-dashboard--pages">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Pages Management</p>
            <h1>Website Pages</h1>
            <p class="admin-dashboard__intro">Review the database-backed pages and open their content editors from one place.</p>
        </div>
    </header>

    <?php if (($pagesState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($pagesState['type'] ?? 'info') ?>">
            <?= e($pagesState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="admin-panel admin-panel--pages-list">
        <div class="admin-panel__header admin-panel__header--inline admin-events-panel__header">
            <div>
                <h2>Pages Library</h2>
                <p class="admin-events-panel__subcopy">Each row below reflects a public page already powered by MySQL page-section data.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) count($pagesList)) ?> pages</span>
        </div>

        <div class="admin-pages-table">
            <?php foreach ($pagesList as $pageItem): ?>
                <article class="admin-pages-row">
                    <div class="admin-pages-row__main">
                        <h3><?= e($pageItem['title'] ?? '') ?></h3>
                        <p><?= e($pageItem['path'] ?? '') ?></p>
                    </div>

                    <div class="admin-pages-row__meta">
                        <span><?= e((string) ($pageItem['section_count'] ?? 0)) ?> sections</span>
                        <small><?= e($pageItem['updated_label'] ?? 'Pending') ?></small>
                    </div>

                    <div class="admin-pages-row__status">
                        <span class="admin-gallery-card__status admin-gallery-card__status--<?= e($pageItem['status'] ?? 'draft') ?>">
                            <?= e(ucfirst((string) ($pageItem['status'] ?? 'draft'))) ?>
                        </span>
                    </div>

                    <div class="admin-pages-row__actions">
                        <a href="<?= e($pageItem['public_href'] ?? route_url('/')) ?>" target="_blank" rel="noreferrer">View</a>
                        <?php if (! empty($pageItem['editable']) && is_string($pageItem['edit_href'] ?? null)): ?>
                            <a href="<?= e($pageItem['edit_href']) ?>">Edit</a>
                        <?php else: ?>
                            <span class="admin-pages-row__coming-soon">Coming Next</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>
