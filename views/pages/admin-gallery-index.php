<?php
declare(strict_types=1);

$galleryState = is_array($galleryState ?? null) ? $galleryState : [];
$galleryItems = is_array($galleryItems ?? null) ? $galleryItems : [];
$galleryFilters = is_array($galleryFilters ?? null) ? $galleryFilters : [];
$galleryCategoryFilters = is_array($galleryCategoryFilters ?? null) ? $galleryCategoryFilters : [];
$galleryPagination = is_array($galleryPagination ?? null) ? $galleryPagination : [];
$gallerySearchTerm = (string) ($gallerySearchTerm ?? '');
$galleryTotalItems = (int) ($galleryTotalItems ?? count($galleryItems));
?>
<section class="admin-dashboard admin-dashboard--gallery">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Media Library</p>
            <h1>Media Library</h1>
            <p class="admin-dashboard__intro">Manage and curate the temple image archive that powers the public Gallery page and Home highlights.</p>
        </div>

        <div class="admin-dashboard__actions">
            <a class="admin-primary-action" href="<?= e(route_url('/admin/media/new')) ?>">+ Add Media Item</a>
        </div>
    </header>

    <?php if (($galleryState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($galleryState['type'] ?? 'info') ?>">
            <?= e($galleryState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="admin-panel admin-panel--gallery-list">
        <div class="admin-panel__header admin-panel__header--inline admin-events-panel__header">
            <div>
                <h2>Gallery Items</h2>
                <p class="admin-events-panel__subcopy">Published gallery items appear on the public Gallery page automatically.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) $galleryTotalItems) ?> records</span>
        </div>

        <div class="admin-events-toolbar">
            <div class="admin-events-toolbar__filters">
                <?php foreach ($galleryFilters as $filter): ?>
                    <a class="admin-events-filter<?= ! empty($filter['active']) ? ' admin-events-filter--active' : '' ?>" href="<?= e($filter['href'] ?? route_url('/admin/media')) ?>">
                        <?= e($filter['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form class="admin-events-search" action="<?= e(route_url('/admin/media')) ?>" method="get">
                <input name="q" type="search" value="<?= e($gallerySearchTerm) ?>" placeholder="Search gallery...">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if ($galleryCategoryFilters !== []): ?>
            <div class="admin-gallery-categories">
                <?php foreach ($galleryCategoryFilters as $filter): ?>
                    <a class="admin-gallery-categories__chip<?= ! empty($filter['active']) ? ' admin-gallery-categories__chip--active' : '' ?>" href="<?= e($filter['href'] ?? route_url('/admin/media')) ?>">
                        <?= e($filter['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($galleryItems === []): ?>
            <p class="admin-panel__empty">No gallery items match the current view yet.</p>
        <?php else: ?>
            <div class="admin-gallery-grid">
                <?php foreach ($galleryItems as $item): ?>
                    <article class="admin-gallery-card">
                        <div class="admin-gallery-card__image">
                            <img src="<?= e(asset($item['image_path'] ?? 'assets/images/placeholders/gallery-gopuram.svg')) ?>" alt="<?= e($item['title'] ?? 'Gallery image') ?>">
                        </div>
                        <div class="admin-gallery-card__body">
                            <div class="admin-gallery-card__head">
                                <div>
                                    <h3><?= e($item['title'] ?? '') ?></h3>
                                    <p><?= e($item['category_name'] ?? 'Collection') ?></p>
                                </div>
                                <span class="admin-gallery-card__status admin-gallery-card__status--<?= e($item['status'] ?? 'draft') ?>">
                                    <?= e(ucfirst((string) ($item['status'] ?? 'draft'))) ?>
                                </span>
                            </div>

                            <p class="admin-gallery-card__caption"><?= e($item['caption'] ?? 'No caption added yet.') ?></p>

                            <div class="admin-gallery-card__meta">
                                <span>Sort order: <?= e((string) ($item['sort_order'] ?? 0)) ?></span>
                                <span><?= ! empty($item['is_featured_home']) ? 'Home highlight' : (! empty($item['is_featured']) ? 'Featured item' : 'Standard item') ?></span>
                            </div>

                            <div class="admin-gallery-card__actions">
                                <a href="<?= e(route_url_with_query('/gallery', ['category' => ($item['category_slug'] ?? '') === '' ? null : ($item['category_slug'] ?? '')])) ?>" target="_blank" rel="noreferrer">Preview</a>
                                <a href="<?= e(route_url('/admin/media/' . (string) ($item['id'] ?? 0) . '/edit')) ?>">Edit</a>
                                <form action="<?= e(route_url('/admin/media/' . (string) ($item['id'] ?? 0) . '/delete')) ?>" method="post">
                                    <?= csrf_input() ?>
                                    <button type="submit" onclick="return confirm('Delete this gallery item?');">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (($galleryPagination['pages'] ?? []) !== [] && count($galleryPagination['pages']) > 1): ?>
            <div class="admin-events-pagination">
                <?php if (($galleryPagination['previous']['href'] ?? null) !== null): ?>
                    <a class="admin-events-pagination__arrow" href="<?= e($galleryPagination['previous']['href']) ?>">Previous</a>
                <?php endif; ?>

                <?php foreach ($galleryPagination['pages'] as $pageNumber): ?>
                    <a class="admin-events-pagination__page<?= ! empty($pageNumber['active']) ? ' admin-events-pagination__page--active' : '' ?>" href="<?= e($pageNumber['href'] ?? route_url('/admin/media')) ?>">
                        <?= e($pageNumber['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>

                <?php if (($galleryPagination['next']['href'] ?? null) !== null): ?>
                    <a class="admin-events-pagination__arrow" href="<?= e($galleryPagination['next']['href']) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
