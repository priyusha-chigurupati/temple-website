<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$contactItem = is_array($contactItem ?? null) ? $contactItem : [];
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center / Contact / View</p>
            <h1>Contact Inquiry</h1>
            <p class="admin-dashboard__intro">Review the full message and mark it as reviewed when the temple team has handled it.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/submissions/contact')) ?>">Back to Contact Inquiries</a>
        </div>
    </header>

    <?php if (($submissionState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($submissionState['type'] ?? 'info') ?>">
            <?= e($submissionState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <div class="admin-page-editor">
        <section class="admin-form-panel admin-submission-detail">
            <div class="admin-panel__header admin-panel__header--inline">
                <div>
                    <h2><?= e((string) ($contactItem['full_name'] ?? '')) ?></h2>
                    <p class="admin-events-panel__subcopy"><?= e((string) ($contactItem['subject'] ?? '')) ?></p>
                </div>
                <span class="admin-events-phase admin-events-phase--<?= e((string) ($contactItem['status'] ?? 'pending')) ?>">
                    <?= e(ucfirst((string) ($contactItem['status'] ?? 'pending'))) ?>
                </span>
            </div>
            <div class="admin-submission-detail__meta-grid">
                <div class="admin-field"><label>Email</label><input type="text" value="<?= e((string) ($contactItem['email'] ?? '')) ?>" disabled></div>
                <div class="admin-field"><label>Submitted</label><input type="text" value="<?= e(date('M d, Y h:i A', strtotime((string) ($contactItem['created_at'] ?? 'now')))) ?>" disabled></div>
            </div>
            <div class="admin-field">
                <label>Message</label>
                <textarea rows="8" disabled><?= e((string) ($contactItem['message'] ?? '')) ?></textarea>
            </div>
        </section>
    </div>

    <div class="admin-form-actions">
        <a class="admin-secondary-action" href="<?= e(route_url('/admin/submissions/contact')) ?>">Back</a>
        <?php if (($contactItem['status'] ?? 'pending') !== 'reviewed'): ?>
            <form action="<?= e(route_url('/admin/submissions/contact/' . (string) ($contactItem['id'] ?? 0) . '/review')) ?>" method="post">
                <?= csrf_input() ?>
                <button class="admin-primary-action" type="submit">Mark Reviewed</button>
            </form>
        <?php endif; ?>
    </div>
</section>
