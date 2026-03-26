<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$donationItem = is_array($donationItem ?? null) ? $donationItem : [];
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center / Donations / View</p>
            <h1>Donation Notice</h1>
            <p class="admin-dashboard__intro">Review the full donation notice details recorded from the public site.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/submissions/donations')) ?>">Back to Donation Notices</a>
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
                    <h2><?= e((string) ($donationItem['full_name'] ?? '')) ?></h2>
                    <p class="admin-events-panel__subcopy"><?= e((string) ($donationItem['payment_method'] ?? 'Donation notice')) ?> &middot; Rs <?= e(number_format((float) ($donationItem['amount'] ?? 0), 2)) ?></p>
                </div>
                <span class="admin-events-phase admin-events-phase--<?= e((string) ($donationItem['status'] ?? 'pending')) ?>">
                    <?= e(ucfirst((string) ($donationItem['status'] ?? 'pending'))) ?>
                </span>
            </div>
            <div class="admin-submission-detail__meta-grid">
                <div class="admin-field"><label>Reference ID</label><input type="text" value="<?= e((string) (($donationItem['reference_id'] ?? '') !== '' ? $donationItem['reference_id'] : 'Not provided')) ?>" disabled></div>
                <div class="admin-field"><label>Phone</label><input type="text" value="<?= e((string) ($donationItem['phone'] ?? '')) ?>" disabled></div>
                <div class="admin-field"><label>Email</label><input type="text" value="<?= e((string) (($donationItem['email'] ?? '') !== '' ? $donationItem['email'] : 'Not provided')) ?>" disabled></div>
                <div class="admin-field"><label>Submitted</label><input type="text" value="<?= e(date('M d, Y h:i A', strtotime((string) ($donationItem['created_at'] ?? 'now')))) ?>" disabled></div>
            </div>
            <div class="admin-field">
                <label>Address</label>
                <textarea rows="3" disabled><?= e((string) ($donationItem['address'] ?? '')) ?></textarea>
            </div>
            <div class="admin-form-panel__two-up">
                <div class="admin-field">
                    <label>Purpose</label>
                    <input type="text" value="<?= e((string) (($donationItem['purpose'] ?? '') !== '' ? $donationItem['purpose'] : 'Not provided')) ?>" disabled>
                </div>
                <div class="admin-field">
                    <label>Message</label>
                    <textarea rows="3" disabled><?= e((string) (($donationItem['message'] ?? '') !== '' ? $donationItem['message'] : 'No message included.')) ?></textarea>
                </div>
            </div>
        </section>
    </div>

    <div class="admin-form-actions">
        <a class="admin-secondary-action" href="<?= e(route_url('/admin/submissions/donations')) ?>">Back</a>
        <?php if (($donationItem['status'] ?? 'pending') !== 'reviewed'): ?>
            <form action="<?= e(route_url('/admin/submissions/donations/' . (string) ($donationItem['id'] ?? 0) . '/review')) ?>" method="post">
                <?= csrf_input() ?>
                <button class="admin-primary-action" type="submit">Mark Reviewed</button>
            </form>
        <?php endif; ?>
    </div>
</section>
