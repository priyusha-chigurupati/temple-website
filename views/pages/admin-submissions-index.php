<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$submissionCounts = is_array($submissionCounts ?? null) ? $submissionCounts : [];
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center</p>
            <h1>Submission Center</h1>
            <p class="admin-dashboard__intro">Review the messages, notices, uploads, and subscriptions coming in from the public website.</p>
        </div>
    </header>

    <?php if (($submissionState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($submissionState['type'] ?? 'info') ?>">
            <?= e($submissionState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <div class="admin-submissions-grid">
        <a class="admin-submission-card" href="<?= e(route_url('/admin/submissions/contact')) ?>">
            <span class="admin-submission-card__label">Contact Inquiries</span>
            <strong><?= e((string) ($submissionCounts['contact_total'] ?? 0)) ?></strong>
            <p><?= e((string) ($submissionCounts['contact_pending'] ?? 0)) ?> pending review.</p>
        </a>
        <a class="admin-submission-card" href="<?= e(route_url('/admin/submissions/donations')) ?>">
            <span class="admin-submission-card__label">Donation Notices</span>
            <strong><?= e((string) ($submissionCounts['donation_total'] ?? 0)) ?></strong>
            <p><?= e((string) ($submissionCounts['donation_pending'] ?? 0)) ?> pending review.</p>
        </a>
        <a class="admin-submission-card" href="<?= e(route_url('/admin/submissions/gallery')) ?>">
            <span class="admin-submission-card__label">Gallery Uploads</span>
            <strong><?= e((string) ($submissionCounts['gallery_total'] ?? 0)) ?></strong>
            <p><?= e((string) ($submissionCounts['gallery_pending'] ?? 0)) ?> waiting for review.</p>
        </a>
        <a class="admin-submission-card" href="<?= e(route_url('/admin/submissions/newsletter')) ?>">
            <span class="admin-submission-card__label">Newsletter List</span>
            <strong><?= e((string) ($submissionCounts['newsletter_total'] ?? 0)) ?></strong>
            <p><?= e((string) ($submissionCounts['newsletter_active'] ?? 0)) ?> currently active.</p>
        </a>
    </div>
</section>
