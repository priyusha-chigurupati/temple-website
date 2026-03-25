<?php
declare(strict_types=1);
?>

<section class="events-page">
    <div class="container">
        <header class="events-page__header">
            <div class="events-page__heading">
                <p class="eyebrow"><?= e($page['eyebrow']) ?></p>
                <h1 class="events-page__title"><?= e($page['title']) ?></h1>
            </div>
            <p class="events-page__description"><?= e($page['description']) ?></p>
        </header>

        <div class="events-page__divider" aria-hidden="true"></div>

        <section class="events-section">
            <div class="events-section__head">
                <h2>Ongoing Events</h2>
                <div class="events-section__line"></div>
                <span class="events-section__dot" aria-hidden="true"></span>
                <span class="events-section__status">Live Now</span>
            </div>

            <div class="events-ongoing-list">
                <?php foreach ($page['ongoing'] as $item): ?>
                    <article class="events-ongoing-card events-ongoing-card--<?= e($item['layout']) ?>">
                        <div class="events-ongoing-card__image">
                            <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['title']) ?> placeholder artwork">
                        </div>
                        <div class="events-ongoing-card__body">
                            <div class="events-ongoing-card__meta">
                                <span class="events-badge"><?= e($item['label']) ?></span>
                                <span class="events-date"><?= e($item['date']) ?></span>
                            </div>
                            <h3><?= e($item['title']) ?></h3>
                            <p><?= e($item['description']) ?></p>
                            <a class="text-link text-link--arrow" href="#">
                                <?= e($item['cta']) ?>
                                <span class="text-link__arrow" aria-hidden="true"></span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <section class="events-upcoming">
        <div class="container">
            <div class="events-upcoming__head">
                <div>
                    <h2>Upcoming Festivals</h2>
                    <p>Mark your calendar for these auspicious dates</p>
                </div>
                <a class="section-link" href="#">View Full Calendar</a>
            </div>

            <div class="events-upcoming__grid">
                <?php foreach ($page['upcoming'] as $item): ?>
                    <article class="events-upcoming-card">
                        <div class="events-upcoming-card__image">
                            <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['title']) ?> placeholder artwork">
                            <div class="events-upcoming-card__date">
                                <strong><?= e($item['day']) ?></strong>
                                <span><?= e($item['month']) ?></span>
                            </div>
                        </div>
                        <div class="events-upcoming-card__body">
                            <h3><?= e($item['title']) ?></h3>
                            <p><?= e($item['description']) ?></p>
                            <a class="button button--outline button--full" href="#"><?= e($item['cta']) ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="events-sponsor">
        <div class="container events-sponsor__inner">
            <h2><?= e($page['sponsor_cta']['title']) ?></h2>
            <p><?= e($page['sponsor_cta']['description']) ?></p>
            <div class="events-sponsor__actions">
                <a class="button button--primary" href="#"><?= e($page['sponsor_cta']['primary']) ?></a>
                <a class="button button--outline" href="#"><?= e($page['sponsor_cta']['secondary']) ?></a>
            </div>
        </div>
    </section>
</section>
