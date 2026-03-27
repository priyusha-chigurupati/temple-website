<?php
declare(strict_types=1);

$pageState = is_array($pageState ?? null) ? $pageState : [];
$contactForm = is_array($contactForm ?? null) ? $contactForm : [];
$pageData = is_array($contactForm['page'] ?? null) ? $contactForm['page'] : [];
$hero = is_array($contactForm['hero'] ?? null) ? $contactForm['hero'] : [];
$info = is_array($contactForm['info'] ?? null) ? $contactForm['info'] : [];
$form = is_array($contactForm['form'] ?? null) ? $contactForm['form'] : [];
$map = is_array($contactForm['map'] ?? null) ? $contactForm['map'] : [];
?>
<section class="admin-dashboard admin-dashboard--page-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Pages / Edit Contact</p>
            <h1>Edit Contact Page</h1>
            <p class="admin-dashboard__intro">Update the Contact page hero, info panels, form copy, and map details without disturbing the approved layout.</p>
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

    <form class="admin-events-form" action="<?= e(route_url('/admin/pages/contact')) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Page Meta</h2>
                        <p class="admin-events-panel__subcopy">Internal title and SEO fields for the Contact page.</p>
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
                        <p class="admin-events-panel__subcopy">Top banner title, copy, and image for the Contact page.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-title">Title</label>
                        <input id="hero-title" name="hero_title" type="text" value="<?= e((string) ($hero['title'] ?? '')) ?>" required>
                    </div>
                    <div>
                        <?php
                        $mediaFieldName = 'hero_image';
                        $mediaFieldId = 'contact-hero-image';
                        $mediaFieldLabel = 'Hero Image';
                        $mediaCurrentPath = (string) ($hero['image'] ?? '');
                        $mediaSelectName = 'hero_image_media_id';
                        $mediaUploadName = 'hero_image_upload';
                        $mediaRequired = true;
                        $mediaHelp = 'Upload or select the Contact page hero image.';
                        $mediaPreviewAlt = 'Contact hero image preview';
                        require __DIR__ . '/../partials/admin-media-field.php';
                        ?>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="hero-description">Description</label>
                    <textarea id="hero-description" name="hero_description" rows="3"><?= e((string) ($hero['description'] ?? '')) ?></textarea>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Visit Information</h2>
                        <p class="admin-events-panel__subcopy">Three info cards plus the social links block shown beside the form.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="info-eyebrow">Eyebrow</label>
                        <input id="info-eyebrow" name="info_eyebrow" type="text" value="<?= e((string) ($info['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="info-title">Section Title</label>
                        <input id="info-title" name="info_title" type="text" value="<?= e((string) ($info['title'] ?? '')) ?>" required>
                    </div>
                </div>

                <?php foreach ([
                    'one' => 'Location',
                    'two' => 'Hours',
                    'three' => 'Direct Reach',
                ] as $key => $label): ?>
                    <div class="admin-form-panel__three-up">
                        <div class="admin-field">
                            <label for="info-item-<?= e($key) ?>-icon"><?= e($label) ?> Icon</label>
                            <input id="info-item-<?= e($key) ?>-icon" name="info_item_<?= e($key) ?>_icon" type="text" value="<?= e((string) ($info['item_' . $key . '_icon'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="info-item-<?= e($key) ?>-title"><?= e($label) ?> Title</label>
                            <input id="info-item-<?= e($key) ?>-title" name="info_item_<?= e($key) ?>_title" type="text" value="<?= e((string) ($info['item_' . $key . '_title'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="info-item-<?= e($key) ?>-line-one-text"><?= e($label) ?> Line One</label>
                            <input id="info-item-<?= e($key) ?>-line-one-text" name="info_item_<?= e($key) ?>_line_one_text" type="text" value="<?= e((string) ($info['item_' . $key . '_line_one_text'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="info-item-<?= e($key) ?>-line-two-text"><?= e($label) ?> Line Two</label>
                            <input id="info-item-<?= e($key) ?>-line-two-text" name="info_item_<?= e($key) ?>_line_two_text" type="text" value="<?= e((string) ($info['item_' . $key . '_line_two_text'] ?? '')) ?>">
                        </div>
                        <?php if ($key !== 'two'): ?>
                            <div class="admin-field">
                                <label for="info-item-<?= e($key) ?>-line-one-href"><?= e($label) ?> Link / Action</label>
                                <input id="info-item-<?= e($key) ?>-line-one-href" name="info_item_<?= e($key) ?>_line_one_href" type="text" value="<?= e((string) ($info['item_' . $key . '_line_one_href'] ?? '')) ?>">
                            </div>
                        <?php else: ?>
                            <div class="admin-field">
                                <label>&nbsp;</label>
                                <input type="text" value="Hours block uses plain text only." readonly>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($key !== 'two'): ?>
                        <div class="admin-field">
                            <label for="info-item-<?= e($key) ?>-line-two-href"><?= e($label) ?> Second Link / Action</label>
                            <input id="info-item-<?= e($key) ?>-line-two-href" name="info_item_<?= e($key) ?>_line_two_href" type="text" value="<?= e((string) ($info['item_' . $key . '_line_two_href'] ?? '')) ?>">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="admin-field">
                    <label for="social-title">Social Title</label>
                    <input id="social-title" name="social_title" type="text" value="<?= e((string) ($info['social_title'] ?? '')) ?>">
                </div>
                <?php foreach ([
                    'one' => 'First',
                    'two' => 'Second',
                    'three' => 'Third',
                ] as $key => $label): ?>
                    <div class="admin-form-panel__three-up">
                        <div class="admin-field">
                            <label for="social-<?= e($key) ?>-label"><?= e($label) ?> Label</label>
                            <input id="social-<?= e($key) ?>-label" name="social_<?= e($key) ?>_label" type="text" value="<?= e((string) ($info['social_' . $key . '_label'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="social-<?= e($key) ?>-short"><?= e($label) ?> Short</label>
                            <input id="social-<?= e($key) ?>-short" name="social_<?= e($key) ?>_short" type="text" value="<?= e((string) ($info['social_' . $key . '_short'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="social-<?= e($key) ?>-href"><?= e($label) ?> Href</label>
                            <input id="social-<?= e($key) ?>-href" name="social_<?= e($key) ?>_href" type="text" value="<?= e((string) ($info['social_' . $key . '_href'] ?? '')) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Message Form Copy</h2>
                        <p class="admin-events-panel__subcopy">Editable labels and placeholders for the public contact form.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="form-title">Form Title</label>
                        <input id="form-title" name="form_title" type="text" value="<?= e((string) ($form['title'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="form-button">Button Label</label>
                        <input id="form-button" name="form_button" type="text" value="<?= e((string) ($form['button'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="form-description">Form Description</label>
                    <textarea id="form-description" name="form_description" rows="3"><?= e((string) ($form['description'] ?? '')) ?></textarea>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="form-full-name-label">Full Name Label</label>
                        <input id="form-full-name-label" name="form_full_name_label" type="text" value="<?= e((string) ($form['full_name_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="form-full-name-placeholder">Full Name Placeholder</label>
                        <input id="form-full-name-placeholder" name="form_full_name_placeholder" type="text" value="<?= e((string) ($form['full_name_placeholder'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="form-email-label">Email Label</label>
                        <input id="form-email-label" name="form_email_label" type="text" value="<?= e((string) ($form['email_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="form-email-placeholder">Email Placeholder</label>
                        <input id="form-email-placeholder" name="form_email_placeholder" type="text" value="<?= e((string) ($form['email_placeholder'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="form-subject-label">Subject Label</label>
                        <input id="form-subject-label" name="form_subject_label" type="text" value="<?= e((string) ($form['subject_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="form-subject-one">Subject Option One</label>
                        <input id="form-subject-one" name="form_subject_option_one" type="text" value="<?= e((string) ($form['subject_option_one'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="form-subject-two">Subject Option Two</label>
                        <input id="form-subject-two" name="form_subject_option_two" type="text" value="<?= e((string) ($form['subject_option_two'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="form-subject-three">Subject Option Three</label>
                        <input id="form-subject-three" name="form_subject_option_three" type="text" value="<?= e((string) ($form['subject_option_three'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="form-subject-four">Subject Option Four</label>
                        <input id="form-subject-four" name="form_subject_option_four" type="text" value="<?= e((string) ($form['subject_option_four'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="form-message-label">Message Label</label>
                        <input id="form-message-label" name="form_message_label" type="text" value="<?= e((string) ($form['message_label'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="form-message-placeholder">Message Placeholder</label>
                        <input id="form-message-placeholder" name="form_message_placeholder" type="text" value="<?= e((string) ($form['message_placeholder'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Map Section</h2>
                        <p class="admin-events-panel__subcopy">Map headline, CTA, map artwork, and marker label.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="map-eyebrow">Eyebrow</label>
                        <input id="map-eyebrow" name="map_eyebrow" type="text" value="<?= e((string) ($map['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="map-title">Title</label>
                        <input id="map-title" name="map_title" type="text" value="<?= e((string) ($map['title'] ?? '')) ?>" required>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="map-cta">CTA Label</label>
                        <input id="map-cta" name="map_cta" type="text" value="<?= e((string) ($map['cta'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="map-href">CTA Href</label>
                        <input id="map-href" name="map_href" type="text" value="<?= e((string) ($map['href'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div>
                        <?php
                        $mediaFieldName = 'map_image';
                        $mediaFieldId = 'contact-map-image';
                        $mediaFieldLabel = 'Map Image';
                        $mediaCurrentPath = (string) ($map['image'] ?? '');
                        $mediaSelectName = 'map_image_media_id';
                        $mediaUploadName = 'map_image_upload';
                        $mediaRequired = true;
                        $mediaHelp = 'Upload or choose the artwork used for the Contact map section.';
                        $mediaPreviewAlt = 'Contact map image preview';
                        require __DIR__ . '/../partials/admin-media-field.php';
                        ?>
                    </div>
                    <div class="admin-field">
                        <label for="map-marker">Marker Label</label>
                        <input id="map-marker" name="map_marker" type="text" value="<?= e((string) ($map['marker'] ?? '')) ?>">
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/pages')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save Contact Page</button>
        </div>
    </form>
</section>
