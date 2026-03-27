<?php
declare(strict_types=1);
?>
<?php $hero = $page['hero']; ?>
<?php $about = $page['about_section']; ?>
<?php $events = $page['events_section']; ?>
<?php $gallery = $page['gallery_section']; ?>
<?php $donation = $page['donation_section']; ?>

<section class="hero">
    <div class="hero__backdrop">
        <?php if (($hero['background_image'] ?? '') !== ''): ?>
            <img src="<?= e(asset($hero['background_image'])) ?>" alt="" role="presentation">
        <?php endif; ?>
    </div>
    <div class="container hero__grid">
        <div class="hero__copy">
            <p class="eyebrow"><?= e($hero['eyebrow']) ?></p>
            <h1 class="hero__title">
                <span class="hero__title-line"><?= e($hero['title_prefix']) ?></span>
                <span class="hero__title-highlight"><?= e($hero['title_highlight']) ?></span>
                <span class="hero__title-line"><?= e($hero['title_suffix']) ?></span>
            </h1>
            <p class="hero__description"><?= e($hero['description']) ?></p>
            <div class="hero__actions">
                <a class="button button--gradient" href="<?= e(route_url($hero['primary_cta']['href'])) ?>"><?= e($hero['primary_cta']['label']) ?></a>
                <a class="button button--ghost" href="<?= e(route_url($hero['secondary_cta']['href'])) ?>"><?= e($hero['secondary_cta']['label']) ?></a>
            </div>
        </div>
        <div class="hero__visual">
            <div class="hero__frame">
                <img src="<?= e(asset($hero['feature_image'])) ?>" alt="Decorative temple-themed placeholder artwork for the featured Home page panel">
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="spiritual-divider"></div>
</div>

<div class="home-sections">
    <section class="section section--about section--home-about">
        <div class="container section-grid">
            <div class="about-media">
                <div class="about-media__panel">
                    <img src="<?= e(asset($about['image'])) ?>" alt="Temple architecture placeholder artwork used while final media is pending">
                </div>
            </div>
            <div class="about-copy">
                <h2 class="section-title"><?= e($about['title']) ?></h2>
                <?php foreach ($about['description'] as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
                <a class="text-link text-link--arrow" href="<?= e(route_url($about['cta']['href'])) ?>">
                    <?= e($about['cta']['label']) ?>
                    <span class="text-link__arrow" aria-hidden="true"></span>
                </a>
            </div>
        </div>
    </section>

    <section class="section section--events section--home-events">
        <div class="container">
            <div class="section-heading">
                <div>
                    <p class="eyebrow"><?= e($events['eyebrow']) ?></p>
                    <h2 class="section-title"><?= e($events['title']) ?></h2>
                </div>
                <a class="section-link" href="<?= e(route_url($events['cta']['href'])) ?>"><?= e($events['cta']['label']) ?></a>
            </div>
            <div class="card-grid card-grid--events">
                <?php foreach ($events['items'] as $item): ?>
                    <article class="card card--event">
                        <div class="card__media">
                            <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['title']) ?> placeholder artwork">
                        </div>
                        <div class="card__body">
                            <p class="card__meta"><?= e($item['date']) ?></p>
                            <h3 class="card__title"><?= e($item['title']) ?></h3>
                            <p class="card__text"><?= e($item['description']) ?></p>
                            <a class="text-link" href="<?= e(route_url($item['href'])) ?>">Event Details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section--gallery section--home-gallery">
        <div class="container">
            <div class="section-heading section-heading--center">
                <div>
                    <h2 class="section-title"><?= e($gallery['title']) ?></h2>
                </div>
            </div>
            <div class="masonry-grid">
                <?php foreach ($gallery['items'] as $index => $item): ?>
                    <figure class="masonry-tile<?= $index === 0 ? ' masonry-tile--featured' : '' ?>">
                        <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['label']) ?> placeholder artwork">
                        <?php if ($index === 0): ?>
                            <figcaption><?= e($item['label']) ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
            <div class="section-action">
                <a class="button button--surface" href="<?= e(route_url($gallery['cta']['href'])) ?>"><?= e($gallery['cta']['label']) ?></a>
            </div>
        </div>
    </section>

    <section class="section section--donation section--home-donation">
        <div class="container donation-grid">
            <div class="donation-copy">
                <h2 class="section-title section-title--light"><?= e($donation['title']) ?></h2>
                <p class="donation-copy__text"><?= e($donation['description']) ?></p>
                <div class="donation-card-row">
                    <?php foreach ($donation['cards'] as $item): ?>
                        <article class="donation-card">
                            <h3><?= e($item['title']) ?></h3>
                            <p><?= e($item['description']) ?></p>
                            <a class="button button--outline" href="<?= e(route_url('/donations')) ?>"><?= e($item['button']) ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <aside class="quick-panel" aria-label="Quick contribution">
                <h2>Quick Contribution</h2>
                <form class="quick-panel__form" action="<?= e(route_url($donation['cta']['href'])) ?>" method="get">
                    <div class="quick-panel__items">
                        <?php foreach ($donation['quick_options'] as $index => $item): ?>
                            <?php $optionId = 'quick-option-' . $index; ?>
                            <label class="quick-option<?= $item['type'] === 'custom' ? ' quick-option--custom' : '' ?>" for="<?= e($optionId) ?>">
                                <input
                                    id="<?= e($optionId) ?>"
                                    class="quick-option__input"
                                    type="radio"
                                    name="purpose"
                                    value="<?= e($item['purpose']) ?>"
                                    <?= $index === 0 ? 'checked' : '' ?>
                                >
                                <span class="quick-option__content">
                                    <span><?= e($item['label']) ?></span>
                                    <?php if ($item['type'] !== 'custom'): ?>
                                        <strong><?= e($item['amount']) ?></strong>
                                    <?php endif; ?>
                                </span>
                                <?php if ($item['type'] === 'custom'): ?>
                                    <input
                                        class="quick-option__amount"
                                        type="text"
                                        name="custom_amount"
                                        inputmode="decimal"
                                        placeholder="<?= e($item['amount']) ?>"
                                        aria-label="<?= e($item['label']) ?> amount"
                                    >
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button class="button button--primary button--full" type="submit"><?= e($donation['cta']['label']) ?></button>
                </form>
            </aside>
        </div>
    </section>
</div>
