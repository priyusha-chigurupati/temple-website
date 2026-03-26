<?php
declare(strict_types=1);

$blogState = is_array($blogState ?? null) ? $blogState : [];
$blogPosts = is_array($blogPosts ?? null) ? $blogPosts : [];
$blogFilters = is_array($blogFilters ?? null) ? $blogFilters : [];
$blogCategoryFilters = is_array($blogCategoryFilters ?? null) ? $blogCategoryFilters : [];
$blogPagination = is_array($blogPagination ?? null) ? $blogPagination : [];
$blogStats = is_array($blogStats ?? null) ? $blogStats : [];
$latestPost = is_array($latestPost ?? null) ? $latestPost : null;
$blogSearchTerm = (string) ($blogSearchTerm ?? '');
$blogTotalItems = (int) ($blogTotalItems ?? count($blogPosts));
?>
<section class="admin-dashboard admin-dashboard--blog">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Blog Library</p>
            <h1>Blog Library</h1>
            <p class="admin-dashboard__intro">Manage featured articles, journal entries, and category-driven content for the public Blog page.</p>
        </div>

        <div class="admin-dashboard__actions">
            <a class="admin-primary-action" href="<?= e(route_url('/admin/blog/new')) ?>">+ Add Blog Article</a>
        </div>
    </header>

    <?php if (($blogState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($blogState['type'] ?? 'info') ?>">
            <?= e($blogState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="admin-events-stats">
        <article class="admin-events-stat admin-events-stat--wide">
            <div>
                <span class="admin-events-stat__label">Published Articles</span>
                <strong><?= e((string) ($blogStats['published'] ?? 0)) ?></strong>
            </div>
            <div>
                <span class="admin-events-stat__label">Drafts</span>
                <strong><?= e((string) ($blogStats['draft'] ?? 0)) ?></strong>
            </div>
            <div>
                <span class="admin-events-stat__label">Featured</span>
                <strong><?= e((string) ($blogStats['featured'] ?? 0)) ?></strong>
            </div>
        </article>

        <article class="admin-events-stat">
            <span class="admin-events-stat__label">Latest Article</span>
            <strong><?= e($latestPost['title'] ?? 'No articles yet') ?></strong>
            <p><?= e($latestPost['date_label'] ?? 'Publishing schedule pending') ?></p>
        </article>
    </section>

    <section class="admin-panel admin-panel--blog-list">
        <div class="admin-panel__header admin-panel__header--inline admin-events-panel__header">
            <div>
                <h2>Article Listing</h2>
                <p class="admin-events-panel__subcopy">All article records below are connected to the public Blog page.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) $blogTotalItems) ?> records</span>
        </div>

        <div class="admin-events-toolbar">
            <div class="admin-events-toolbar__filters">
                <?php foreach ($blogFilters as $filter): ?>
                    <a class="admin-events-filter<?= ! empty($filter['active']) ? ' admin-events-filter--active' : '' ?>" href="<?= e($filter['href'] ?? route_url('/admin/blog')) ?>">
                        <?= e($filter['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form class="admin-events-search" action="<?= e(route_url('/admin/blog')) ?>" method="get">
                <input name="q" type="search" value="<?= e($blogSearchTerm) ?>" placeholder="Search articles...">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if ($blogCategoryFilters !== []): ?>
            <div class="admin-gallery-categories">
                <?php foreach ($blogCategoryFilters as $filter): ?>
                    <a class="admin-gallery-categories__chip<?= ! empty($filter['active']) ? ' admin-gallery-categories__chip--active' : '' ?>" href="<?= e($filter['href'] ?? route_url('/admin/blog')) ?>">
                        <?= e($filter['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($blogPosts === []): ?>
            <p class="admin-panel__empty">No articles match the current view yet.</p>
        <?php else: ?>
            <div class="admin-blog-grid">
                <?php foreach ($blogPosts as $post): ?>
                    <article class="admin-blog-card">
                        <div class="admin-blog-card__image">
                            <img src="<?= e(asset($post['image_path'] ?? 'assets/images/placeholders/blog-post-deepam.svg')) ?>" alt="<?= e($post['title'] ?? 'Blog image') ?>">
                        </div>
                        <div class="admin-blog-card__body">
                            <div class="admin-blog-card__head">
                                <div>
                                    <h3><?= e($post['title'] ?? '') ?></h3>
                                    <p><?= e($post['category_name'] ?? 'Temple Journal') ?></p>
                                </div>
                                <span class="admin-gallery-card__status admin-gallery-card__status--<?= e($post['status'] ?? 'draft') ?>">
                                    <?= e(ucfirst((string) ($post['status'] ?? 'draft'))) ?>
                                </span>
                            </div>

                            <p class="admin-blog-card__excerpt"><?= e($post['excerpt'] ?? 'No excerpt added yet.') ?></p>

                            <div class="admin-blog-card__meta">
                                <span><?= e($post['date_label'] ?? 'Schedule pending') ?></span>
                                <span><?= e($post['read_time_label'] ?? '6 Min Read') ?></span>
                                <span><?= ! empty($post['is_featured']) ? 'Featured article' : 'Standard article' ?></span>
                            </div>

                            <div class="admin-blog-card__slug">
                                <code>/blog/<?= e($post['slug'] ?? '') ?></code>
                            </div>

                            <div class="admin-gallery-card__actions">
                                <?php if (($post['slug'] ?? '') !== ''): ?>
                                    <a href="<?= e(route_url('/blog/' . $post['slug'])) ?>" target="_blank" rel="noreferrer">View</a>
                                <?php endif; ?>
                                <a href="<?= e(route_url('/admin/blog/' . (string) ($post['id'] ?? 0) . '/edit')) ?>">Edit</a>
                                <form action="<?= e(route_url('/admin/blog/' . (string) ($post['id'] ?? 0) . '/delete')) ?>" method="post">
                                    <?= csrf_input() ?>
                                    <button type="submit" onclick="return confirm('Delete this article?');">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (($blogPagination['pages'] ?? []) !== [] && count($blogPagination['pages']) > 1): ?>
            <div class="admin-events-pagination">
                <?php if (($blogPagination['previous']['href'] ?? null) !== null): ?>
                    <a class="admin-events-pagination__arrow" href="<?= e($blogPagination['previous']['href']) ?>">Previous</a>
                <?php endif; ?>

                <?php foreach ($blogPagination['pages'] as $pageNumber): ?>
                    <a class="admin-events-pagination__page<?= ! empty($pageNumber['active']) ? ' admin-events-pagination__page--active' : '' ?>" href="<?= e($pageNumber['href'] ?? route_url('/admin/blog')) ?>">
                        <?= e($pageNumber['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>

                <?php if (($blogPagination['next']['href'] ?? null) !== null): ?>
                    <a class="admin-events-pagination__arrow" href="<?= e($blogPagination['next']['href']) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
