<?php
declare(strict_types=1);
?>

<section class="gallery-page">
    <div class="container">
        <header class="gallery-page__header">
            <h1 class="gallery-page__title">
                Visual Chronicles of
                <span><?= e($page['highlight']) ?></span>
            </h1>
            <p class="gallery-page__description"><?= e($page['description']) ?></p>
        </header>

        <div class="gallery-filters" aria-label="Gallery categories">
            <?php foreach ($page['filters'] as $filter): ?>
                <button class="gallery-filter<?= $filter['active'] ? ' is-active' : '' ?>" type="button">
                    <?= e($filter['label']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <section class="gallery-masonry" aria-label="Temple gallery">
            <?php foreach ($page['items'] as $item): ?>
                <article class="gallery-card gallery-card--<?= e($item['size']) ?>">
                    <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['title']) ?> placeholder artwork">
                    <div class="gallery-card__overlay">
                        <span class="gallery-card__category"><?= e($item['category']) ?></span>
                        <h2 class="gallery-card__title"><?= e($item['title']) ?></h2>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="gallery-divider" aria-hidden="true"></div>

        <section class="gallery-cta">
            <h2><?= e($page['cta']['title']) ?></h2>
            <p><?= e($page['cta']['description']) ?></p>
            <a class="button button--primary" href="#"><?= e($page['cta']['button']) ?></a>
        </section>
    </div>
</section>
