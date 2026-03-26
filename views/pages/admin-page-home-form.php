<?php
declare(strict_types=1);

$pageState = is_array($pageState ?? null) ? $pageState : [];
$homeForm = is_array($homeForm ?? null) ? $homeForm : [];
$pageData = is_array($homeForm['page'] ?? null) ? $homeForm['page'] : [];
$hero = is_array($homeForm['hero'] ?? null) ? $homeForm['hero'] : [];
$about = is_array($homeForm['about_preview'] ?? null) ? $homeForm['about_preview'] : [];
$events = is_array($homeForm['events_preview'] ?? null) ? $homeForm['events_preview'] : [];
$gallery = is_array($homeForm['gallery_preview'] ?? null) ? $homeForm['gallery_preview'] : [];
$donation = is_array($homeForm['donation_preview'] ?? null) ? $homeForm['donation_preview'] : [];
?>
<section class="admin-dashboard admin-dashboard--page-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Pages / Edit Home</p>
            <h1>Edit Home Page</h1>
            <p class="admin-dashboard__intro">Update the Home page meta content and each editable section without changing the approved layout.</p>
        </div>

        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/pages')) ?>">Back to Pages</a>
        </div>
    </header>

    <?php if (($pageState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($pageState['type'] ?? 'info') ?>">
            <?= e($pageState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e(route_url('/admin/pages/home')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Page Meta</h2>
                        <p class="admin-events-panel__subcopy">Internal title and SEO fields for the Home page.</p>
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="page-title">Internal Page Title</label>
                        <input id="page-title" name="page_title" type="text" value="<?= e((string) ($pageData['title'] ?? '')) ?>" required>
                    </div>

                    <div class="admin-field">
                        <label for="meta-title">Meta Title</label>
                        <input id="meta-title" name="meta_title" type="text" value="<?= e((string) ($pageData['meta_title'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="meta-description">Meta Description</label>
                    <textarea id="meta-description" name="meta_description" rows="3"><?= e((string) ($pageData['meta_description'] ?? '')) ?></textarea>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Hero Section</h2>
                        <p class="admin-events-panel__subcopy">Controls the main Home page welcome area.</p>
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-eyebrow">Eyebrow</label>
                        <input id="hero-eyebrow" name="hero_eyebrow" type="text" value="<?= e((string) ($hero['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-feature-image">Feature Image Path</label>
                        <input id="hero-feature-image" name="hero_feature_image" type="text" value="<?= e((string) ($hero['feature_image'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="hero-title-prefix">Title Prefix</label>
                        <input id="hero-title-prefix" name="hero_title_prefix" type="text" value="<?= e((string) ($hero['title_prefix'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-title-highlight">Title Highlight</label>
                        <input id="hero-title-highlight" name="hero_title_highlight" type="text" value="<?= e((string) ($hero['title_highlight'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="hero-title-suffix">Title Suffix</label>
                        <input id="hero-title-suffix" name="hero_title_suffix" type="text" value="<?= e((string) ($hero['title_suffix'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="hero-description">Description</label>
                    <textarea id="hero-description" name="hero_description" rows="4"><?= e((string) ($hero['description'] ?? '')) ?></textarea>
                </div>

                <div class="admin-field">
                    <label for="hero-background-image">Background Image Path</label>
                    <input id="hero-background-image" name="hero_background_image" type="text" value="<?= e((string) ($hero['background_image'] ?? '')) ?>">
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-primary-label">Primary CTA Label</label>
                        <input id="hero-primary-label" name="hero_primary_cta_label" type="text" value="<?= e((string) ($hero['primary_cta_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-primary-href">Primary CTA Href</label>
                        <input id="hero-primary-href" name="hero_primary_cta_href" type="text" value="<?= e((string) ($hero['primary_cta_href'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-secondary-label">Secondary CTA Label</label>
                        <input id="hero-secondary-label" name="hero_secondary_cta_label" type="text" value="<?= e((string) ($hero['secondary_cta_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-secondary-href">Secondary CTA Href</label>
                        <input id="hero-secondary-href" name="hero_secondary_cta_href" type="text" value="<?= e((string) ($hero['secondary_cta_href'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>About Preview</h2>
                        <p class="admin-events-panel__subcopy">Controls the preview block that leads into the About page.</p>
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="about-title">Section Title</label>
                        <input id="about-title" name="about_title" type="text" value="<?= e((string) ($about['title'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="about-image">Image Path</label>
                        <input id="about-image" name="about_image" type="text" value="<?= e((string) ($about['image'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="about-paragraphs">Paragraphs</label>
                    <textarea id="about-paragraphs" name="about_paragraphs" rows="6"><?= e((string) ($about['paragraphs'] ?? '')) ?></textarea>
                    <small>Separate paragraphs with a blank line.</small>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="about-cta-label">CTA Label</label>
                        <input id="about-cta-label" name="about_cta_label" type="text" value="<?= e((string) ($about['cta_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="about-cta-href">CTA Href</label>
                        <input id="about-cta-href" name="about_cta_href" type="text" value="<?= e((string) ($about['cta_href'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Events Preview</h2>
                        <p class="admin-events-panel__subcopy">Only the section heading and CTA are editable here. Event cards come from the Events module.</p>
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="events-eyebrow">Eyebrow</label>
                        <input id="events-eyebrow" name="events_eyebrow" type="text" value="<?= e((string) ($events['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="events-title">Section Title</label>
                        <input id="events-title" name="events_title" type="text" value="<?= e((string) ($events['title'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="events-cta-label">CTA Label</label>
                        <input id="events-cta-label" name="events_cta_label" type="text" value="<?= e((string) ($events['cta_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="events-cta-href">CTA Href</label>
                        <input id="events-cta-href" name="events_cta_href" type="text" value="<?= e((string) ($events['cta_href'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Gallery Preview</h2>
                        <p class="admin-events-panel__subcopy">The heading and CTA live here. Gallery items still come from the Gallery module.</p>
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="gallery-title">Section Title</label>
                        <input id="gallery-title" name="gallery_title" type="text" value="<?= e((string) ($gallery['title'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="gallery-cta-label">CTA Label</label>
                        <input id="gallery-cta-label" name="gallery_cta_label" type="text" value="<?= e((string) ($gallery['cta_label'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="gallery-cta-href">CTA Href</label>
                    <input id="gallery-cta-href" name="gallery_cta_href" type="text" value="<?= e((string) ($gallery['cta_href'] ?? '')) ?>">
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Donation Preview</h2>
                        <p class="admin-events-panel__subcopy">Preview text and cards for the Home donation section.</p>
                    </div>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="donation-title">Section Title</label>
                        <input id="donation-title" name="donation_title" type="text" value="<?= e((string) ($donation['title'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="donation-cta-label">Form CTA Label</label>
                        <input id="donation-cta-label" name="donation_cta_label" type="text" value="<?= e((string) ($donation['cta_label'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="donation-description">Description</label>
                    <textarea id="donation-description" name="donation_description" rows="4"><?= e((string) ($donation['description'] ?? '')) ?></textarea>
                </div>

                <div class="admin-field">
                    <label for="donation-cta-href">Form CTA Href</label>
                    <input id="donation-cta-href" name="donation_cta_href" type="text" value="<?= e((string) ($donation['cta_href'] ?? '')) ?>">
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="donation-card-one-title">Card One Title</label>
                        <input id="donation-card-one-title" name="donation_card_one_title" type="text" value="<?= e((string) ($donation['card_one_title'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-card-one-button">Card One Button</label>
                        <input id="donation-card-one-button" name="donation_card_one_button" type="text" value="<?= e((string) ($donation['card_one_button'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="donation-card-one-description">Card One Description</label>
                    <textarea id="donation-card-one-description" name="donation_card_one_description" rows="3"><?= e((string) ($donation['card_one_description'] ?? '')) ?></textarea>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="donation-card-two-title">Card Two Title</label>
                        <input id="donation-card-two-title" name="donation_card_two_title" type="text" value="<?= e((string) ($donation['card_two_title'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-card-two-button">Card Two Button</label>
                        <input id="donation-card-two-button" name="donation_card_two_button" type="text" value="<?= e((string) ($donation['card_two_button'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="donation-card-two-description">Card Two Description</label>
                    <textarea id="donation-card-two-description" name="donation_card_two_description" rows="3"><?= e((string) ($donation['card_two_description'] ?? '')) ?></textarea>
                </div>

                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="donation-quick-one-label">Quick Option One Label</label>
                        <input id="donation-quick-one-label" name="donation_quick_one_label" type="text" value="<?= e((string) ($donation['quick_one_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-quick-one-purpose">Quick Option One Purpose</label>
                        <input id="donation-quick-one-purpose" name="donation_quick_one_purpose" type="text" value="<?= e((string) ($donation['quick_one_purpose'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-quick-one-amount">Quick Option One Amount</label>
                        <input id="donation-quick-one-amount" name="donation_quick_one_amount" type="text" value="<?= e((string) ($donation['quick_one_amount'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="donation-quick-two-label">Quick Option Two Label</label>
                        <input id="donation-quick-two-label" name="donation_quick_two_label" type="text" value="<?= e((string) ($donation['quick_two_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-quick-two-purpose">Quick Option Two Purpose</label>
                        <input id="donation-quick-two-purpose" name="donation_quick_two_purpose" type="text" value="<?= e((string) ($donation['quick_two_purpose'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-quick-two-amount">Quick Option Two Amount</label>
                        <input id="donation-quick-two-amount" name="donation_quick_two_amount" type="text" value="<?= e((string) ($donation['quick_two_amount'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="donation-quick-three-label">Quick Option Three Label</label>
                        <input id="donation-quick-three-label" name="donation_quick_three_label" type="text" value="<?= e((string) ($donation['quick_three_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-quick-three-purpose">Quick Option Three Purpose</label>
                        <input id="donation-quick-three-purpose" name="donation_quick_three_purpose" type="text" value="<?= e((string) ($donation['quick_three_purpose'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="donation-quick-three-amount">Quick Option Three Amount / Placeholder</label>
                        <input id="donation-quick-three-amount" name="donation_quick_three_amount" type="text" value="<?= e((string) ($donation['quick_three_amount'] ?? '')) ?>">
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/pages')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save Home Page</button>
        </div>
    </form>
</section>
