<?php
declare(strict_types=1);

$pageState = is_array($pageState ?? null) ? $pageState : [];
$donationsForm = is_array($donationsForm ?? null) ? $donationsForm : [];
$pageData = is_array($donationsForm['page'] ?? null) ? $donationsForm['page'] : [];
$hero = is_array($donationsForm['hero'] ?? null) ? $donationsForm['hero'] : [];
$methods = is_array($donationsForm['methods'] ?? null) ? $donationsForm['methods'] : [];
$instructions = is_array($donationsForm['instructions'] ?? null) ? $donationsForm['instructions'] : [];
$form = is_array($donationsForm['form'] ?? null) ? $donationsForm['form'] : [];
?>
<section class="admin-dashboard admin-dashboard--page-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Pages / Edit Donations</p>
            <h1>Edit Donations Page</h1>
            <p class="admin-dashboard__intro">Update the donation guidance page, methods, instructions, and notification form copy without changing the approved layout.</p>
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

    <form class="admin-events-form" action="<?= e(route_url('/admin/pages/donations')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Page Meta</h2>
                        <p class="admin-events-panel__subcopy">Internal title and SEO fields for the Donations page.</p>
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
                        <p class="admin-events-panel__subcopy">Top message, impact panel, hero image, and transparency note.</p>
                    </div>
                </div>
                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="hero-eyebrow">Eyebrow</label>
                        <input id="hero-eyebrow" name="hero_eyebrow" type="text" value="<?= e((string) ($hero['eyebrow'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-title-prefix">Title Prefix</label>
                        <input id="hero-title-prefix" name="hero_title_prefix" type="text" value="<?= e((string) ($hero['title_prefix'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="hero-title-highlight">Title Highlight</label>
                        <input id="hero-title-highlight" name="hero_title_highlight" type="text" value="<?= e((string) ($hero['title_highlight'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="hero-description">Description</label>
                    <textarea id="hero-description" name="hero_description" rows="3"><?= e((string) ($hero['description'] ?? '')) ?></textarea>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-impact-title">Impact Title</label>
                        <input id="hero-impact-title" name="hero_impact_title" type="text" value="<?= e((string) ($hero['impact_title'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-image">Hero Image Path</label>
                        <input id="hero-image" name="hero_image" type="text" value="<?= e((string) ($hero['hero_image'] ?? '')) ?>" required>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="hero-impact-description">Impact Description</label>
                    <textarea id="hero-impact-description" name="hero_impact_description" rows="3"><?= e((string) ($hero['impact_description'] ?? '')) ?></textarea>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="hero-transparency-stat">Transparency Stat</label>
                        <input id="hero-transparency-stat" name="hero_transparency_stat" type="text" value="<?= e((string) ($hero['transparency_stat'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="hero-transparency-label">Transparency Label</label>
                        <input id="hero-transparency-label" name="hero_transparency_label" type="text" value="<?= e((string) ($hero['transparency_label'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Donation Methods</h2>
                        <p class="admin-events-panel__subcopy">Three method cards used on the public Donations page.</p>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="methods-title">Section Title</label>
                    <input id="methods-title" name="methods_title" type="text" value="<?= e((string) ($methods['title'] ?? '')) ?>" required>
                </div>

                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="method-one-icon">Onsite Icon</label>
                        <input id="method-one-icon" name="method_one_icon" type="text" value="<?= e((string) ($methods['method_one_icon'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-one-tone">Onsite Tone</label>
                        <input id="method-one-tone" name="method_one_tone" type="text" value="<?= e((string) ($methods['method_one_tone'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-one-title">Onsite Title</label>
                        <input id="method-one-title" name="method_one_title" type="text" value="<?= e((string) ($methods['method_one_title'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="method-one-description">Onsite Description</label>
                    <textarea id="method-one-description" name="method_one_description" rows="3"><?= e((string) ($methods['method_one_description'] ?? '')) ?></textarea>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="method-one-form-one">Accepted Form One</label>
                        <input id="method-one-form-one" name="method_one_form_one" type="text" value="<?= e((string) ($methods['method_one_form_one'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-one-form-two">Accepted Form Two</label>
                        <input id="method-one-form-two" name="method_one_form_two" type="text" value="<?= e((string) ($methods['method_one_form_two'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="method-one-form-three">Accepted Form Three</label>
                        <input id="method-one-form-three" name="method_one_form_three" type="text" value="<?= e((string) ($methods['method_one_form_three'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-one-form-four">Accepted Form Four</label>
                        <input id="method-one-form-four" name="method_one_form_four" type="text" value="<?= e((string) ($methods['method_one_form_four'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="method-two-icon">UPI Icon</label>
                        <input id="method-two-icon" name="method_two_icon" type="text" value="<?= e((string) ($methods['method_two_icon'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-two-tone">UPI Tone</label>
                        <input id="method-two-tone" name="method_two_tone" type="text" value="<?= e((string) ($methods['method_two_tone'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-two-title">UPI Title</label>
                        <input id="method-two-title" name="method_two_title" type="text" value="<?= e((string) ($methods['method_two_title'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="method-two-description">UPI Description</label>
                        <textarea id="method-two-description" name="method_two_description" rows="3"><?= e((string) ($methods['method_two_description'] ?? '')) ?></textarea>
                    </div>
                    <div class="admin-field">
                        <label for="method-two-code">UPI Code</label>
                        <input id="method-two-code" name="method_two_code" type="text" value="<?= e((string) ($methods['method_two_code'] ?? '')) ?>">
                    </div>
                </div>

                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="method-three-icon">Bank Icon</label>
                        <input id="method-three-icon" name="method_three_icon" type="text" value="<?= e((string) ($methods['method_three_icon'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-three-tone">Bank Tone</label>
                        <input id="method-three-tone" name="method_three_tone" type="text" value="<?= e((string) ($methods['method_three_tone'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="method-three-title">Bank Title</label>
                        <input id="method-three-title" name="method_three_title" type="text" value="<?= e((string) ($methods['method_three_title'] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="method-three-description">Bank Description</label>
                    <textarea id="method-three-description" name="method_three_description" rows="3"><?= e((string) ($methods['method_three_description'] ?? '')) ?></textarea>
                </div>
                <?php foreach ([
                    'one' => 'First',
                    'two' => 'Second',
                    'three' => 'Third',
                ] as $key => $label): ?>
                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="method-three-detail-<?= e($key) ?>-label"><?= e($label) ?> Detail Label</label>
                            <input id="method-three-detail-<?= e($key) ?>-label" name="method_three_detail_<?= e($key) ?>_label" type="text" value="<?= e((string) ($methods['method_three_detail_' . $key . '_label'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="method-three-detail-<?= e($key) ?>-value"><?= e($label) ?> Detail Value</label>
                            <input id="method-three-detail-<?= e($key) ?>-value" name="method_three_detail_<?= e($key) ?>_value" type="text" value="<?= e((string) ($methods['method_three_detail_' . $key . '_value'] ?? '')) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Instructions and Support Message</h2>
                        <p class="admin-events-panel__subcopy">Three instruction steps and the support note shown beside the notify form.</p>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="instructions-title">Section Title</label>
                    <input id="instructions-title" name="instructions_title" type="text" value="<?= e((string) ($instructions['title'] ?? '')) ?>" required>
                </div>
                <?php foreach ([
                    'one' => 'First',
                    'two' => 'Second',
                    'three' => 'Third',
                ] as $key => $label): ?>
                    <div class="admin-form-panel__three-up">
                        <div class="admin-field">
                            <label for="instructions-item-<?= e($key) ?>-number"><?= e($label) ?> Number</label>
                            <input id="instructions-item-<?= e($key) ?>-number" name="instructions_item_<?= e($key) ?>_number" type="text" value="<?= e((string) ($instructions['item_' . $key . '_number'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="instructions-item-<?= e($key) ?>-title"><?= e($label) ?> Title</label>
                            <input id="instructions-item-<?= e($key) ?>-title" name="instructions_item_<?= e($key) ?>_title" type="text" value="<?= e((string) ($instructions['item_' . $key . '_title'] ?? '')) ?>">
                        </div>
                        <div class="admin-field">
                            <label for="instructions-item-<?= e($key) ?>-description"><?= e($label) ?> Description</label>
                            <input id="instructions-item-<?= e($key) ?>-description" name="instructions_item_<?= e($key) ?>_description" type="text" value="<?= e((string) ($instructions['item_' . $key . '_description'] ?? '')) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="benefit-title">Support Note Title</label>
                        <input id="benefit-title" name="benefit_title" type="text" value="<?= e((string) ($instructions['benefit_title'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="benefit-description">Support Note Description</label>
                        <input id="benefit-description" name="benefit_description" type="text" value="<?= e((string) ($instructions['benefit_description'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Notify Form Copy</h2>
                        <p class="admin-events-panel__subcopy">Editable labels and placeholders for the donation notice form. Payment method values stay fixed for logic consistency.</p>
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
                <div class="admin-field">
                    <label for="form-reference-note">Reference Note</label>
                    <textarea id="form-reference-note" name="form_reference_note" rows="3"><?= e((string) ($form['reference_note'] ?? '')) ?></textarea>
                </div>
                <?php foreach ([
                    'full_name' => ['label' => 'Full Name', 'placeholder' => 'Full Name Placeholder'],
                    'payment' => ['label' => 'Payment Method', 'placeholder' => null],
                    'amount' => ['label' => 'Amount', 'placeholder' => 'Amount Placeholder'],
                    'reference' => ['label' => 'Reference', 'placeholder' => 'Reference Placeholder'],
                    'phone' => ['label' => 'Phone', 'placeholder' => 'Phone Placeholder'],
                    'email' => ['label' => 'Email', 'placeholder' => 'Email Placeholder'],
                    'address' => ['label' => 'Address', 'placeholder' => 'Address Placeholder'],
                    'message' => ['label' => 'Message', 'placeholder' => 'Message Placeholder'],
                ] as $key => $labels): ?>
                    <?php if ($key === 'payment'): ?>
                        <div class="admin-form-panel__two-up">
                            <div class="admin-field">
                                <label for="form-payment-label"><?= e($labels['label']) ?> Label</label>
                                <input id="form-payment-label" name="form_payment_label" type="text" value="<?= e((string) ($form['payment_label'] ?? '')) ?>">
                            </div>
                            <div class="admin-field">
                                <label for="form-payment-option-one">First Option Label</label>
                                <input id="form-payment-option-one" name="form_payment_option_one_label" type="text" value="<?= e((string) ($form['payment_option_one_label'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="admin-form-panel__two-up">
                            <div class="admin-field">
                                <label for="form-payment-option-two">Second Option Label</label>
                                <input id="form-payment-option-two" name="form_payment_option_two_label" type="text" value="<?= e((string) ($form['payment_option_two_label'] ?? '')) ?>">
                            </div>
                            <div class="admin-field">
                                <label for="form-payment-option-three">Third Option Label</label>
                                <input id="form-payment-option-three" name="form_payment_option_three_label" type="text" value="<?= e((string) ($form['payment_option_three_label'] ?? '')) ?>">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="admin-form-panel__two-up">
                            <div class="admin-field">
                                <label for="form-<?= e($key) ?>-label"><?= e($labels['label']) ?> Label</label>
                                <input id="form-<?= e($key) ?>-label" name="form_<?= e($key) ?>_label" type="text" value="<?= e((string) ($form[$key . '_label'] ?? '')) ?>">
                            </div>
                            <div class="admin-field">
                                <label for="form-<?= e($key) ?>-placeholder"><?= e((string) $labels['placeholder']) ?></label>
                                <input id="form-<?= e($key) ?>-placeholder" name="form_<?= e($key) ?>_placeholder" type="text" value="<?= e((string) ($form[$key . '_placeholder'] ?? '')) ?>">
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/pages')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save Donations Page</button>
        </div>
    </form>
</section>
