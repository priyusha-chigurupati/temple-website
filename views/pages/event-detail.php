<?php
declare(strict_types=1);
?>

<section class="event-detail-page">
    <div class="container">
        <a class="text-link text-link--arrow event-detail-page__back" href="<?= e($page['back_href']) ?>">
            <span class="text-link__arrow" aria-hidden="true"></span>
            <?= e($page['back_label']) ?>
        </a>

        <div class="event-detail-hero">
            <div class="event-detail-hero__copy">
                <p class="eyebrow"><?= e($page['eyebrow']) ?></p>
                <h1><?= e($page['title']) ?></h1>
                <p class="event-detail-hero__description"><?= e($page['description']) ?></p>

                <div class="event-detail-hero__meta">
                    <span class="events-badge"><?= e($page['status_badge']) ?></span>
                    <div class="event-detail-hero__schedule">
                        <span><?= e($page['schedule_label']) ?></span>
                        <strong><?= e($page['schedule']) ?></strong>
                    </div>
                </div>
            </div>

            <div class="event-detail-hero__image">
                <img src="<?= e(asset($page['image'])) ?>" alt="<?= e($page['title']) ?> placeholder artwork">
            </div>
        </div>

        <div class="event-detail-body">
            <article class="event-detail-body__copy">
                <h2>Event Overview</h2>
                <?php foreach ($page['body'] as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </article>

            <aside class="event-detail-facts" aria-label="Event quick facts">
                <h2>Quick Facts</h2>
                <dl>
                    <?php foreach ($page['quick_facts'] as $fact): ?>
                        <div class="event-detail-facts__item">
                            <dt><?= e($fact['label']) ?></dt>
                            <dd><?= e($fact['value']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </aside>
        </div>
    </div>
</section>
