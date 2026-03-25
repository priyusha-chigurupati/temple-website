<?php
declare(strict_types=1);
?>

<section class="blog-detail-page">
    <div class="container">
        <a class="text-link text-link--arrow blog-detail-page__back" href="<?= e($page['back_href']) ?>">
            <span class="text-link__arrow" aria-hidden="true"></span>
            <?= e($page['back_label']) ?>
        </a>

        <article class="blog-detail-hero">
            <div class="blog-detail-hero__copy">
                <p class="eyebrow"><?= e($page['eyebrow']) ?></p>
                <h1><?= e($page['title']) ?></h1>
                <p class="blog-detail-hero__excerpt"><?= e($page['excerpt']) ?></p>
                <div class="blog-feature__meta">
                    <span><?= e($page['date']) ?></span>
                    <span class="blog-feature__dot" aria-hidden="true"></span>
                    <span><?= e($page['read_time']) ?></span>
                    <span class="blog-feature__dot" aria-hidden="true"></span>
                    <span><?= e($page['category']) ?></span>
                </div>
            </div>
            <div class="blog-detail-hero__image">
                <img src="<?= e(asset($page['image'])) ?>" alt="<?= e($page['title']) ?> placeholder artwork">
            </div>
        </article>

        <div class="blog-detail-layout">
            <article class="blog-detail-body">
                <?php foreach ($page['body'] as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </article>

            <aside class="blog-detail-related">
                <h2><?= e($page['related_title']) ?></h2>
                <div class="blog-recent">
                    <?php foreach ($page['related_posts'] as $post): ?>
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
            </aside>
        </div>
    </div>
</section>
