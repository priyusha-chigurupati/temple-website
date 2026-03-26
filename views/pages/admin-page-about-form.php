<?php
declare(strict_types=1);

$pageState = is_array($pageState ?? null) ? $pageState : [];
$aboutForm = is_array($aboutForm ?? null) ? $aboutForm : [];
$pageData = is_array($aboutForm['page'] ?? null) ? $aboutForm['page'] : [];
$hero = is_array($aboutForm['hero'] ?? null) ? $aboutForm['hero'] : [];
$history = is_array($aboutForm['history'] ?? null) ? $aboutForm['history'] : [];
$mission = is_array($aboutForm['mission'] ?? null) ? $aboutForm['mission'] : [];
$values = is_array($aboutForm['values'] ?? null) ? $aboutForm['values'] : [];
$facts = is_array($aboutForm['facts'] ?? null) ? $aboutForm['facts'] : [];
?>
<section class="admin-dashboard admin-dashboard--page-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Pages / Edit About</p>
            <h1>Edit About Page</h1>
            <p class="admin-dashboard__intro">Update the About page story sections and supporting cards without changing the approved layout.</p>
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

    <form class="admin-events-form" action="<?= e(route_url('/admin/pages/about')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Page Meta</h2>
                        <p class="admin-events-panel__subcopy">Internal title and SEO fields for the About page.</p>
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
                        <p class="admin-events-panel__subcopy">Controls the first About page statement and artwork.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-eyebrow">Eyebrow</label>
                        <input id="hero-eyebrow" name="hero_eyebrow" type="text" value="<?= e((string) ($hero['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-image">Hero Image Path</label>
                        <input id="hero-image" name="hero_image" type="text" value="<?= e((string) ($hero['image'] ?? '')) ?>" required>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-title">Title</label>
                        <input id="hero-title" name="hero_title" type="text" value="<?= e((string) ($hero['title'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="hero-highlight">Highlight</label>
                        <input id="hero-highlight" name="hero_highlight" type="text" value="<?= e((string) ($hero['highlight'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>History Section</h2>
                        <p class="admin-events-panel__subcopy">Main story copy, quote, and supporting image pair.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="history-eyebrow">Eyebrow</label>
                        <input id="history-eyebrow" name="history_eyebrow" type="text" value="<?= e((string) ($history['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="history-title">Section Title</label>
                        <input id="history-title" name="history_title" type="text" value="<?= e((string) ($history['title'] ?? '')) ?>" required>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="history-paragraphs">Paragraphs</label>
                    <textarea id="history-paragraphs" name="history_paragraphs" rows="7"><?= e((string) ($history['paragraphs'] ?? '')) ?></textarea>
                    <small>Separate paragraphs with a blank line.</small>
                </div>
                <div class="admin-field">
                    <label for="history-quote">Quote</label>
                    <textarea id="history-quote" name="history_quote" rows="3"><?= e((string) ($history['quote'] ?? '')) ?></textarea>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="history-main-image">Main Image Path</label>
                        <input id="history-main-image" name="history_main_image" type="text" value="<?= e((string) ($history['main_image'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="history-secondary-image">Secondary Image Path</label>
                        <input id="history-secondary-image" name="history_secondary_image" type="text" value="<?= e((string) ($history['secondary_image'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="history-secondary-title">Secondary Title</label>
                        <input id="history-secondary-title" name="history_secondary_title" type="text" value="<?= e((string) ($history['secondary_title'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="history-secondary-text">Secondary Text</label>
                        <input id="history-secondary-text" name="history_secondary_text" type="text" value="<?= e((string) ($history['secondary_text'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Mission Cards</h2>
                        <p class="admin-events-panel__subcopy">Three mission cards shown in the About page mission section.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="mission-eyebrow">Eyebrow</label>
                        <input id="mission-eyebrow" name="mission_eyebrow" type="text" value="<?= e((string) ($mission['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="mission-title">Section Title</label>
                        <input id="mission-title" name="mission_title" type="text" value="<?= e((string) ($mission['title'] ?? '')) ?>">
                    </div>
                </div>
                <?php foreach ([
                    'one' => 'First',
                    'two' => 'Second',
                    'three' => 'Third',
                ] as $key => $label): ?>
                    <div class="admin-form-panel__three-up">
                        <div class="admin-field">
                            <label for="mission-item-<?= e($key) ?>-symbol"><?= e($label) ?> Symbol</label>
                            <input id="mission-item-<?= e($key) ?>-symbol" name="mission_item_<?= e($key) ?>_symbol" type="text" value="<?= e((string) ($mission['item_' . $key . '_symbol'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="mission-item-<?= e($key) ?>-title"><?= e($label) ?> Title</label>
                            <input id="mission-item-<?= e($key) ?>-title" name="mission_item_<?= e($key) ?>_title" type="text" value="<?= e((string) ($mission['item_' . $key . '_title'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="mission-item-<?= e($key) ?>-description"><?= e($label) ?> Description</label>
                            <input id="mission-item-<?= e($key) ?>-description" name="mission_item_<?= e($key) ?>_description" type="text" value="<?= e((string) ($mission['item_' . $key . '_description'] ?? '')) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Values Section</h2>
                        <p class="admin-events-panel__subcopy">Intro panel, highlight image, and four values cards.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="values-intro-title">Intro Title</label>
                        <input id="values-intro-title" name="values_intro_title" type="text" value="<?= e((string) ($values['intro_title'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="values-feature-image">Feature Image Path</label>
                        <input id="values-feature-image" name="values_feature_image" type="text" value="<?= e((string) ($values['feature_image'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="values-intro-text">Intro Text</label>
                    <textarea id="values-intro-text" name="values_intro_text" rows="3"><?= e((string) ($values['intro_text'] ?? '')) ?></textarea>
                </div>
                <?php foreach ([
                    'one' => 'First',
                    'two' => 'Second',
                    'three' => 'Third',
                    'four' => 'Fourth',
                ] as $key => $label): ?>
                    <div class="admin-form-panel__three-up">
                        <div class="admin-field">
                            <label for="values-item-<?= e($key) ?>-number"><?= e($label) ?> Number</label>
                            <input id="values-item-<?= e($key) ?>-number" name="values_item_<?= e($key) ?>_number" type="text" value="<?= e((string) ($values['item_' . $key . '_number'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="values-item-<?= e($key) ?>-title"><?= e($label) ?> Title</label>
                            <input id="values-item-<?= e($key) ?>-title" name="values_item_<?= e($key) ?>_title" type="text" value="<?= e((string) ($values['item_' . $key . '_title'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="values-item-<?= e($key) ?>-description"><?= e($label) ?> Description</label>
                            <input id="values-item-<?= e($key) ?>-description" name="values_item_<?= e($key) ?>_description" type="text" value="<?= e((string) ($values['item_' . $key . '_description'] ?? '')) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Facts Grid</h2>
                        <p class="admin-events-panel__subcopy">Four fact cards shown at the bottom of the About page.</p>
                    </div>
                </div>
                <?php foreach ([
                    'one' => 'First',
                    'two' => 'Second',
                    'three' => 'Third',
                    'four' => 'Fourth',
                ] as $key => $label): ?>
                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="fact-<?= e($key) ?>-value"><?= e($label) ?> Value</label>
                            <input id="fact-<?= e($key) ?>-value" name="fact_<?= e($key) ?>_value" type="text" value="<?= e((string) ($facts['fact_' . $key . '_value'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="fact-<?= e($key) ?>-label"><?= e($label) ?> Label</label>
                            <input id="fact-<?= e($key) ?>-label" name="fact_<?= e($key) ?>_label" type="text" value="<?= e((string) ($facts['fact_' . $key . '_label'] ?? '')) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/pages')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save About Page</button>
        </div>
    </form>
</section>
