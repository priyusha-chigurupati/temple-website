<?php
declare(strict_types=1);

$settingsState = is_array($settingsState ?? null) ? $settingsState : [];
$footerForm = is_array($footerForm ?? null) ? $footerForm : [];
$quickLinks = is_array($footerForm['quick_links'] ?? null) ? $footerForm['quick_links'] : [];
$addressLines = is_array($footerForm['address_lines'] ?? null) ? $footerForm['address_lines'] : ['', '', ''];
$socialLinks = is_array($footerForm['social_links'] ?? null) ? $footerForm['social_links'] : [];
$legalLinks = is_array($footerForm['legal_links'] ?? null) ? $footerForm['legal_links'] : [];
?>
<section class="admin-dashboard admin-dashboard--settings-editor">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Settings / Footer</p>
            <h1>Edit Footer Settings</h1>
            <p class="admin-dashboard__intro">Update the shared footer content, newsletter copy, links, timings, and social profiles for the public site.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/settings')) ?>">Back to Settings</a>
        </div>
    </header>

    <?php if (($settingsState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($settingsState['type'] ?? 'info') ?>">
            <?= e($settingsState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e(route_url('/admin/settings/footer')) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-page-editor">
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Brand and Description</h2>
                        <p class="admin-events-panel__subcopy">Temple name and the shared footer description shown in the first footer column.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="site-name">Site Name</label>
                        <input id="site-name" name="site_name" type="text" value="<?= e((string) ($footerForm['site_name'] ?? '')) ?>" required>
                    </div>
                    <div class="admin-field">
                        <label for="newsletter-title">Newsletter Title</label>
                        <input id="newsletter-title" name="newsletter_title" type="text" value="<?= e((string) ($footerForm['newsletter_title'] ?? '')) ?>" required>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="footer-description">Footer Description</label>
                    <textarea id="footer-description" name="description" rows="3" required><?= e((string) ($footerForm['description'] ?? '')) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="newsletter-description">Newsletter Description</label>
                    <textarea id="newsletter-description" name="newsletter_description" rows="3"><?= e((string) ($footerForm['newsletter_description'] ?? '')) ?></textarea>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Quick Links</h2>
                        <p class="admin-events-panel__subcopy">Four primary links shown in the footer navigation column.</p>
                    </div>
                </div>
                <?php foreach ($quickLinks as $index => $link): ?>
                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="quick-link-<?= e((string) ($index + 1)) ?>-label">Link <?= e((string) ($index + 1)) ?> Label</label>
                            <input id="quick-link-<?= e((string) ($index + 1)) ?>-label" name="quick_link_<?= e((string) ($index + 1)) ?>_label" type="text" value="<?= e((string) ($link['label'] ?? '')) ?>" required>
                        </div>
                        <div class="admin-field">
                            <label for="quick-link-<?= e((string) ($index + 1)) ?>-href">Link <?= e((string) ($index + 1)) ?> Href</label>
                            <input id="quick-link-<?= e((string) ($index + 1)) ?>-href" name="quick_link_<?= e((string) ($index + 1)) ?>_href" type="text" value="<?= e((string) ($link['href'] ?? '')) ?>" required>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Address and Timings</h2>
                        <p class="admin-events-panel__subcopy">Footer address lines and the morning and evening temple timings.</p>
                    </div>
                </div>
                <div class="admin-form-panel__three-up">
                    <div class="admin-field">
                        <label for="address-line-1">Address Line One</label>
                        <input id="address-line-1" name="address_line_1" type="text" value="<?= e((string) ($addressLines[0] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="address-line-2">Address Line Two</label>
                        <input id="address-line-2" name="address_line_2" type="text" value="<?= e((string) ($addressLines[1] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="address-line-3">Address Line Three</label>
                        <input id="address-line-3" name="address_line_3" type="text" value="<?= e((string) ($addressLines[2] ?? '')) ?>">
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="morning">Morning Timings</label>
                        <input id="morning" name="morning" type="text" value="<?= e((string) ($footerForm['morning'] ?? '')) ?>">
                    </div>
                    <div class="admin-field">
                        <label for="evening">Evening Timings</label>
                        <input id="evening" name="evening" type="text" value="<?= e((string) ($footerForm['evening'] ?? '')) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Social Links</h2>
                        <p class="admin-events-panel__subcopy">Three social links used in the footer social icons row.</p>
                    </div>
                </div>
                <?php foreach ($socialLinks as $index => $link): ?>
                    <div class="admin-form-panel__three-up">
                        <div class="admin-field">
                            <label for="social-link-<?= e((string) ($index + 1)) ?>-label">Social <?= e((string) ($index + 1)) ?> Label</label>
                            <input id="social-link-<?= e((string) ($index + 1)) ?>-label" name="social_link_<?= e((string) ($index + 1)) ?>_label" type="text" value="<?= e((string) ($link['label'] ?? '')) ?>" required>
                        </div>
                        <div class="admin-field">
                            <label for="social-link-<?= e((string) ($index + 1)) ?>-short">Social <?= e((string) ($index + 1)) ?> Short</label>
                            <input id="social-link-<?= e((string) ($index + 1)) ?>-short" name="social_link_<?= e((string) ($index + 1)) ?>_short" type="text" value="<?= e((string) ($link['short'] ?? '')) ?>" required>
                        </div>
                        <div class="admin-field">
                            <label for="social-link-<?= e((string) ($index + 1)) ?>-href">Social <?= e((string) ($index + 1)) ?> Href</label>
                            <input id="social-link-<?= e((string) ($index + 1)) ?>-href" name="social_link_<?= e((string) ($index + 1)) ?>_href" type="text" value="<?= e((string) ($link['href'] ?? '')) ?>" required>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Legal Links</h2>
                        <p class="admin-events-panel__subcopy">Footer legal links shown in the bottom-right area.</p>
                    </div>
                </div>
                <?php foreach ($legalLinks as $index => $link): ?>
                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="legal-link-<?= e((string) ($index + 1)) ?>-label">Legal <?= e((string) ($index + 1)) ?> Label</label>
                            <input id="legal-link-<?= e((string) ($index + 1)) ?>-label" name="legal_link_<?= e((string) ($index + 1)) ?>_label" type="text" value="<?= e((string) ($link['label'] ?? '')) ?>" required>
                        </div>
                        <div class="admin-field">
                            <label for="legal-link-<?= e((string) ($index + 1)) ?>-href">Legal <?= e((string) ($index + 1)) ?> Href</label>
                            <input id="legal-link-<?= e((string) ($index + 1)) ?>-href" name="legal_link_<?= e((string) ($index + 1)) ?>_href" type="text" value="<?= e((string) ($link['href'] ?? '')) ?>" required>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/settings')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit">Save Footer Settings</button>
        </div>
    </form>
</section>
