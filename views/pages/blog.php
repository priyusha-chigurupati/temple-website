<?php
declare(strict_types=1);
?>
<?php $featured = $page['featured']; ?>
<?php $sidebar = $page['sidebar']; ?>
<?php $newsletterState = $page['newsletter_state'] ?? []; ?>
<?php $newsletterForm = $page['newsletter_form'] ?? []; ?>

<section class="blog-page">
    <div class="container">
        <section class="blog-feature">
            <div class="blog-feature__image">
                <img src="<?= e(asset($featured['image'])) ?>" alt="Temple-themed placeholder artwork for the featured blog article">
            </div>
            <div class="blog-feature__content">
                <p class="eyebrow"><?= e($featured['eyebrow']) ?></p>
                <h1><?= e($featured['title']) ?></h1>
                <p class="blog-feature__description"><?= e($featured['description']) ?></p>
                <div class="blog-feature__meta">
                    <span><?= e($featured['date']) ?></span>
                    <span class="blog-feature__dot" aria-hidden="true"></span>
                    <span><?= e($featured['read_time']) ?></span>
                </div>
                <a class="blog-feature__cta" href="<?= e($featured['href']) ?>"><?= e($featured['cta']) ?></a>
            </div>
        </section>

        <div class="blog-divider">
            <div class="spiritual-divider"></div>
        </div>

        <section class="blog-layout">
            <div class="blog-main">
                <div class="blog-main__head">
                    <h2><?= e($page['chronicles_title']) ?></h2>
                    <span><?= e($page['chronicles_sort']) ?></span>
                </div>

                <div class="blog-grid">
                    <?php if ($page['posts'] === []): ?>
                        <p class="blog-empty"><?= e($page['empty_message']) ?></p>
                    <?php endif; ?>
                    <?php foreach ($page['posts'] as $post): ?>
                        <article class="blog-card">
                            <div class="blog-card__image">
                                <img src="<?= e(asset($post['image'])) ?>" alt="<?= e($post['title']) ?> placeholder artwork">
                            </div>
                            <span class="blog-card__category"><?= e($post['category']) ?></span>
                            <h3><?= e($post['title']) ?></h3>
                            <p><?= e($post['description']) ?></p>
                            <a class="blog-card__cta" href="<?= e($post['href']) ?>"><?= e($post['cta']) ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="blog-pagination" aria-label="Blog pagination">
                    <?php if (! empty($page['pagination']['previous']['href'])): ?>
                        <a href="<?= e($page['pagination']['previous']['href']) ?>" aria-label="Previous page">&lsaquo;</a>
                    <?php else: ?>
                        <span aria-hidden="true">&lsaquo;</span>
                    <?php endif; ?>
                    <?php foreach ($page['pagination']['pages'] as $item): ?>
                        <a class="<?= $item['active'] ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
                    <?php endforeach; ?>
                    <?php if (! empty($page['pagination']['next']['href'])): ?>
                        <a href="<?= e($page['pagination']['next']['href']) ?>" aria-label="Next page">&rsaquo;</a>
                    <?php else: ?>
                        <span aria-hidden="true">&rsaquo;</span>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="blog-sidebar">
                <section class="blog-sidebar__panel blog-sidebar__panel--search">
                    <h2><?= e($sidebar['search_title']) ?></h2>
                    <form class="blog-search" action="<?= e(route_url('/blog')) ?>" method="get">
                        <?php if (($page['selected_category'] ?? 'all') !== 'all'): ?>
                            <input type="hidden" name="category" value="<?= e($page['selected_category']) ?>">
                        <?php endif; ?>
                        <input type="text" name="q" value="<?= e($page['search_term'] ?? '') ?>" placeholder="<?= e($sidebar['search_placeholder']) ?>">
                        <button type="submit" aria-label="Search blog">
                            <span aria-hidden="true">+</span>
                        </button>
                    </form>
                </section>

                <section class="blog-sidebar__section">
                    <h2><?= e($sidebar['recent_title']) ?></h2>
                    <div class="blog-recent">
                        <?php foreach ($sidebar['recent_posts'] as $post): ?>
                            <a class="blog-recent__item" href="<?= e($post['href']) ?>">
                                <div class="blog-recent__thumb">
                                    <img src="<?= e(asset($post['image'])) ?>" alt="<?= e($post['title']) ?> placeholder artwork">
                                </div>
                                <div>
                                    <h3><?= e($post['title']) ?></h3>
                                    <span><?= e($post['date']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="blog-sidebar__panel blog-sidebar__panel--categories">
                    <h2><?= e($sidebar['categories_title']) ?></h2>
                    <ul class="blog-categories">
                        <?php foreach ($sidebar['categories'] as $category): ?>
                            <li>
                                <a class="<?= $category['active'] ? 'is-active' : '' ?>" href="<?= e($category['href']) ?>">
                                    <span><?= e($category['label']) ?></span>
                                    <span>(<?= e((string) $category['count']) ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <section class="blog-newsletter">
                    <h2><?= e($sidebar['newsletter']['title']) ?></h2>
                    <p><?= e($sidebar['newsletter']['description']) ?></p>
                    <?php if (! empty($newsletterState['message'])): ?>
                        <p class="blog-newsletter__status blog-newsletter__status--<?= e($newsletterState['type'] ?? 'info') ?>">
                            <?= e($newsletterState['message']) ?>
                        </p>
                    <?php endif; ?>
                    <form action="<?= e(route_url_with_query('/blog', [
                        'category' => ($page['selected_category'] ?? 'all') === 'all' ? null : ($page['selected_category'] ?? null),
                        'q' => ($page['search_term'] ?? '') === '' ? null : ($page['search_term'] ?? null),
                        'page' => ($page['current_page'] ?? 1) > 1 ? (string) ($page['current_page'] ?? 1) : null,
                    ])) ?>" method="post">
                        <input type="email" name="email" value="<?= e($newsletterForm['email'] ?? '') ?>" placeholder="<?= e($sidebar['newsletter']['placeholder']) ?>">
                        <button type="submit"><?= e($sidebar['newsletter']['button']) ?></button>
                    </form>
                </section>
            </aside>
        </section>
    </div>
</section>
