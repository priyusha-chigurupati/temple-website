<?php
declare(strict_types=1);
?>
<?php $hero = $page['hero']; ?>
<?php $history = $page['history']; ?>
<?php $mission = $page['mission']; ?>
<?php $values = $page['values']; ?>

<section class="about-hero">
    <div class="about-hero__image">
        <img src="<?= e(asset($hero['image'])) ?>" alt="Temple sanctuary placeholder artwork for the About page hero">
    </div>
    <div class="container about-hero__content">
        <p class="eyebrow"><?= e($hero['eyebrow']) ?></p>
        <h1 class="about-hero__title">
            <?= e($hero['title']) ?><br>
            <span><?= e($hero['highlight']) ?></span>
        </h1>
        <div class="about-hero__accent" aria-hidden="true"></div>
    </div>
</section>

<section class="about-history section">
    <div class="container about-history__grid">
        <div class="about-history__copy">
            <p class="about-history__year"><?= e($history['eyebrow']) ?></p>
            <h2 class="section-title"><?= e($history['title']) ?></h2>
            <?php foreach ($history['paragraphs'] as $paragraph): ?>
                <p><?= e($paragraph) ?></p>
            <?php endforeach; ?>
            <blockquote class="about-history__quote"><?= e($history['quote']) ?></blockquote>
        </div>
        <div class="about-history__media">
            <div class="about-history__main-image">
                <img src="<?= e(asset($history['main_image'])) ?>" alt="Temple stone gateway placeholder artwork">
            </div>
            <div class="about-history__detail-grid">
                <div class="about-history__secondary-image">
                    <img src="<?= e(asset($history['secondary_image'])) ?>" alt="Temple interior placeholder artwork">
                </div>
                <div class="about-history__detail-copy">
                    <h3><?= e($history['secondary_title']) ?></h3>
                    <p><?= e($history['secondary_text']) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-mission section">
    <div class="about-mission__glow" aria-hidden="true"></div>
    <div class="container">
        <div class="about-mission__heading">
            <p class="eyebrow"><?= e($mission['eyebrow']) ?></p>
            <h2 class="section-title"><?= e($mission['title']) ?></h2>
        </div>
        <div class="about-mission__cards">
            <?php foreach ($mission['items'] as $item): ?>
                <article class="mission-card">
                    <span class="mission-card__icon"><?= e($item['symbol']) ?></span>
                    <h3><?= e($item['title']) ?></h3>
                    <p><?= e($item['description']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="about-values section">
    <div class="container">
        <div class="about-values__grid">
            <article class="value-panel value-panel--intro">
                <h2 class="section-title"><?= e($values['intro_title']) ?></h2>
                <p><?= e($values['intro_text']) ?></p>
            </article>
            <article class="value-panel value-panel--highlight">
                <div class="value-panel__number"><?= e($values['items'][0]['number']) ?></div>
                <h3><?= e($values['items'][0]['title']) ?></h3>
                <p><?= e($values['items'][0]['description']) ?></p>
            </article>
            <?php if (! empty($values['feature_image'])): ?>
                <figure class="value-panel value-panel--image">
                    <img src="<?= e(asset($values['feature_image'])) ?>" alt="Hindu devotional placeholder artwork for the values section">
                </figure>
            <?php else: ?>
                <div class="value-panel value-panel--image value-panel--image-placeholder" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="value-panel value-panel--double">
                <?php foreach (array_slice($values['items'], 1, 2) as $item): ?>
                    <article>
                        <div class="value-panel__number"><?= e($item['number']) ?></div>
                        <h3><?= e($item['title']) ?></h3>
                        <p><?= e($item['description']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <article class="value-panel value-panel--primary">
                <div class="value-panel__number"><?= e($values['items'][3]['number']) ?></div>
                <h3><?= e($values['items'][3]['title']) ?></h3>
                <p><?= e($values['items'][3]['description']) ?></p>
            </article>
        </div>
    </div>
</section>

<section class="about-facts">
    <div class="container about-facts__grid">
        <?php foreach ($page['facts'] as $fact): ?>
            <article class="fact-card">
                <strong><?= e($fact['value']) ?></strong>
                <span><?= e($fact['label']) ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>
