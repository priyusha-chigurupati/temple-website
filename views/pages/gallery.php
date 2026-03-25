<?php
declare(strict_types=1);
?>
<?php $submissionState = $page['submission_state'] ?? []; ?>
<?php $submissionForm = $page['submission_form'] ?? []; ?>

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
                <a
                    class="gallery-filter<?= $filter['active'] ? ' is-active' : '' ?>"
                    href="<?= e(route_url_with_query('/gallery', ['category' => $filter['slug'] === 'all' ? null : $filter['slug']])) ?>"
                >
                    <?= e($filter['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <section class="gallery-masonry" aria-label="Temple gallery">
            <?php if ($page['items'] === []): ?>
                <p class="gallery-empty">No gallery images are available in this category yet.</p>
            <?php endif; ?>
            <?php foreach ($page['items'] as $index => $item): ?>
                <article class="gallery-card gallery-card--<?= e($item['size']) ?>">
                    <button
                        class="gallery-card__trigger"
                        type="button"
                        data-gallery-item
                        data-index="<?= e((string) $index) ?>"
                        data-image="<?= e(asset($item['image'])) ?>"
                        data-title="<?= e($item['title']) ?>"
                        data-category="<?= e($item['category']) ?>"
                        data-alt="<?= e($item['title']) ?> placeholder artwork"
                    >
                        <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['title']) ?> placeholder artwork">
                        <div class="gallery-card__overlay">
                            <span class="gallery-card__category"><?= e($item['category']) ?></span>
                            <h2 class="gallery-card__title"><?= e($item['title']) ?></h2>
                        </div>
                    </button>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="gallery-divider" aria-hidden="true"></div>

        <section class="gallery-cta" id="gallery-submit">
            <h2><?= e($page['cta']['title']) ?></h2>
            <p><?= e($page['cta']['description']) ?></p>
            <?php if (! empty($submissionState['message'])): ?>
                <p class="gallery-cta__status gallery-cta__status--<?= e($submissionState['type'] ?? 'info') ?>">
                    <?= e($submissionState['message']) ?>
                </p>
            <?php endif; ?>
            <button class="button button--primary" type="button" data-gallery-open-submit>
                <?= e($page['cta']['button']) ?>
            </button>
        </section>
    </div>

    <div class="gallery-lightbox" data-gallery-lightbox hidden>
        <div class="gallery-lightbox__backdrop" data-gallery-close></div>
        <div class="gallery-lightbox__dialog" role="dialog" aria-modal="true" aria-label="Expanded gallery image">
            <button class="gallery-lightbox__close" type="button" aria-label="Close image viewer" data-gallery-close>&times;</button>
            <button class="gallery-lightbox__nav gallery-lightbox__nav--prev" type="button" aria-label="Previous image" data-gallery-prev>&lsaquo;</button>
            <div class="gallery-lightbox__frame">
                <button class="gallery-lightbox__zoom" type="button" data-gallery-zoom>Zoom</button>
                <img src="" alt="" data-gallery-lightbox-image>
            </div>
            <button class="gallery-lightbox__nav gallery-lightbox__nav--next" type="button" aria-label="Next image" data-gallery-next>&rsaquo;</button>
            <div class="gallery-lightbox__meta">
                <span data-gallery-lightbox-category></span>
                <h2 data-gallery-lightbox-title></h2>
            </div>
        </div>
    </div>

    <div
        class="gallery-submit-modal"
        data-gallery-submit-modal
        data-auto-open="<?= ! empty($submissionState['auto_open']) ? 'true' : 'false' ?>"
        hidden
    >
        <div class="gallery-submit-modal__backdrop" data-gallery-submit-close></div>
        <div class="gallery-submit-modal__dialog" role="dialog" aria-modal="true" aria-label="Submit temple gallery photos">
            <button class="gallery-submit-modal__close" type="button" aria-label="Close submission form" data-gallery-submit-close>&times;</button>
            <h2><?= e($page['cta']['form_title']) ?></h2>
            <p><?= e($page['cta']['form_description']) ?></p>
            <form class="gallery-submit-form" action="<?= e(route_url_with_query('/gallery', ['category' => $page['selected_category'] === 'all' ? null : $page['selected_category']])) ?>" method="post" enctype="multipart/form-data">
                <?php foreach (array_slice($page['cta']['fields'], 0, 2) as $field): ?>
                    <label class="gallery-submit-form__field">
                        <span><?= e($field['label']) ?></span>
                        <input
                            type="<?= e($field['name'] === 'email' ? 'email' : 'text') ?>"
                            name="<?= e($field['name']) ?>"
                            placeholder="<?= e($field['placeholder']) ?>"
                            value="<?= e($submissionForm[$field['name']] ?? '') ?>"
                        >
                    </label>
                <?php endforeach; ?>
                <label class="gallery-submit-form__field gallery-submit-form__field--wide">
                    <span><?= e($page['cta']['fields'][2]['label']) ?></span>
                    <textarea name="description" rows="4" placeholder="<?= e($page['cta']['fields'][2]['placeholder']) ?>"><?= e($submissionForm['description'] ?? '') ?></textarea>
                </label>
                <label class="gallery-submit-form__field gallery-submit-form__field--wide">
                    <span>Photos</span>
                    <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
                </label>
                <p class="gallery-submit-form__note"><?= e($page['cta']['note']) ?></p>
                <button class="button button--primary button--full" type="submit"><?= e($page['cta']['submit_label']) ?></button>
            </form>
        </div>
    </div>
</section>
