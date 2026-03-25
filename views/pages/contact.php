<?php
declare(strict_types=1);
?>
<?php $hero = $page['hero']; ?>
<?php $info = $page['info']; ?>
<?php $form = $page['form']; ?>
<?php $map = $page['map']; ?>
<?php $formState = $page['form_state'] ?? []; ?>
<?php $formValues = $page['form_values'] ?? []; ?>

<section class="contact-page">
    <section class="contact-hero">
        <div class="contact-hero__media">
            <img src="<?= e(asset($hero['image'])) ?>" alt="Temple-themed placeholder artwork for the contact page hero banner">
        </div>
        <div class="contact-hero__overlay"></div>
        <div class="container contact-hero__content">
            <h1><?= e($hero['title']) ?></h1>
            <p><?= e($hero['description']) ?></p>
        </div>
    </section>

    <section class="contact-panel">
        <div class="container contact-panel__grid">
            <div class="contact-info">
                <p class="eyebrow"><?= e($info['eyebrow']) ?></p>
                <h2><?= e($info['title']) ?></h2>
                <div class="contact-info__line" aria-hidden="true"></div>

                <div class="contact-info__list">
                    <?php foreach ($info['items'] as $item): ?>
                        <article class="contact-info__item">
                            <div class="contact-info__icon" aria-hidden="true"><?= e($item['icon']) ?></div>
                            <div>
                                <h3><?= e($item['title']) ?></h3>
                                <?php foreach ($item['lines'] as $line): ?>
                                    <?php if (! empty($line['href'])): ?>
                                        <a class="contact-info__link" href="<?= e($line['href']) ?>"><?= e($line['text']) ?></a>
                                    <?php else: ?>
                                        <p><?= e($line['text']) ?></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="contact-social">
                    <h3><?= e($info['social_title']) ?></h3>
                    <div class="contact-social__list">
                        <?php foreach ($info['social_links'] as $link): ?>
                            <a href="<?= e($link['href']) ?>" aria-label="<?= e($link['label']) ?>" target="_blank" rel="noreferrer">
                                <span><?= e($link['short']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="contact-form-card">
                <h2><?= e($form['title']) ?></h2>
                <p><?= e($form['description']) ?></p>
                <?php if (! empty($formState['message'])): ?>
                    <p class="contact-form__status contact-form__status--<?= e($formState['type'] ?? 'info') ?>">
                        <?= e($formState['message']) ?>
                    </p>
                <?php endif; ?>

                <form class="contact-form" action="<?= e(route_url('/contact')) ?>" method="post">
                    <div class="contact-form__grid">
                        <?php foreach ($form['fields'] as $field): ?>
                            <?php $wide = $field['width'] === 'full' ? ' contact-form__field--full' : ''; ?>
                            <?php $value = $formValues[$field['name']] ?? ''; ?>
                            <label class="contact-form__field<?= $wide ?>">
                                <span><?= e($field['label']) ?></span>
                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea name="<?= e($field['name']) ?>" rows="5" placeholder="<?= e($field['placeholder']) ?>"><?= e($value) ?></textarea>
                                <?php elseif ($field['type'] === 'select'): ?>
                                    <select name="<?= e($field['name']) ?>">
                                        <?php foreach ($field['options'] as $option): ?>
                                            <option<?= $value === $option ? ' selected' : '' ?>><?= e($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="<?= e($field['type']) ?>" name="<?= e($field['name']) ?>" placeholder="<?= e($field['placeholder']) ?>" value="<?= e($value) ?>">
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button class="button button--primary button--full" type="submit"><?= e($form['button']) ?></button>
                </form>
            </div>
        </div>
    </section>

    <section class="contact-map">
        <div class="container">
            <div class="contact-map__head">
                <div>
                    <p class="eyebrow"><?= e($map['eyebrow']) ?></p>
                    <h2><?= e($map['title']) ?></h2>
                </div>
                <a class="section-link" href="<?= e($map['href']) ?>" target="_blank" rel="noreferrer">
                    <?= e($map['cta']) ?>
                    <span class="text-link__arrow" aria-hidden="true"></span>
                </a>
            </div>

            <div class="contact-map__frame">
                <img src="<?= e(asset($map['image'])) ?>" alt="Decorative map-style placeholder artwork for the temple location">
                <div class="contact-map__marker">
                    <div class="contact-map__pin" aria-hidden="true">H</div>
                    <div class="contact-map__label"><?= e($map['marker']) ?></div>
                </div>
            </div>
        </div>
    </section>
</section>
