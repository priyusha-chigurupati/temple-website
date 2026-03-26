<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$gallerySubmissionItem = is_array($gallerySubmissionItem ?? null) ? $gallerySubmissionItem : [];
$galleryReviewForm = is_array($galleryReviewForm ?? null) ? $galleryReviewForm : [];
$files = is_array($gallerySubmissionItem['files'] ?? null) ? $gallerySubmissionItem['files'] : [];
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center / Gallery / Review</p>
            <h1>Review Gallery Submission</h1>
            <p class="admin-dashboard__intro">Inspect the uploaded files and record whether this submission is approved or rejected.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/submissions/gallery')) ?>">Back to Gallery Submissions</a>
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
                    <h2><?= e((string) ($gallerySubmissionItem['submitter_name'] ?? '')) ?></h2>
                    <p class="admin-events-panel__subcopy"><?= e((string) ($gallerySubmissionItem['submitter_email'] ?? '')) ?></p>
                </div>
                <span class="admin-events-phase admin-events-phase--<?= e((string) ($gallerySubmissionItem['status'] ?? 'pending')) ?>">
                    <?= e(ucfirst((string) ($gallerySubmissionItem['status'] ?? 'pending'))) ?>
                </span>
            </div>
            <div class="admin-submission-detail__meta-grid">
                <div class="admin-field"><label>Submitted</label><input type="text" value="<?= e(date('M d, Y h:i A', strtotime((string) ($gallerySubmissionItem['created_at'] ?? 'now')))) ?>" disabled></div>
                <div class="admin-field"><label>Reviewed</label><input type="text" value="<?= e((string) (($gallerySubmissionItem['reviewed_at'] ?? '') !== '' ? date('M d, Y h:i A', strtotime((string) $gallerySubmissionItem['reviewed_at'])) : 'Not reviewed yet')) ?>" disabled></div>
            </div>
            <div class="admin-field">
                <label>Description</label>
                <textarea rows="4" disabled><?= e((string) (($gallerySubmissionItem['description'] ?? '') !== '' ? $gallerySubmissionItem['description'] : 'No description provided.')) ?></textarea>
            </div>

            <div class="admin-submission-files">
                <?php foreach ($files as $file): ?>
                    <article class="admin-submission-file">
                        <img src="<?= e(asset((string) ($file['file_path'] ?? ''))) ?>" alt="<?= e((string) ($file['original_name'] ?? 'Submitted file')) ?>">
                        <div>
                            <strong><?= e((string) ($file['original_name'] ?? 'Uploaded file')) ?></strong>
                            <span><?= e((string) ($file['mime_type'] ?? '')) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <form class="admin-events-form" action="<?= e(route_url('/admin/submissions/gallery/' . (string) ($gallerySubmissionItem['id'] ?? 0) . '/review')) ?>" method="post">
            <?= csrf_input() ?>
            <section class="admin-form-panel">
                <div class="admin-panel__header admin-panel__header--inline">
                    <div>
                        <h2>Review Decision</h2>
                        <p class="admin-events-panel__subcopy">Choose the outcome now. Publishing approved submissions into the public gallery can still be handled later.</p>
                    </div>
                </div>
                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="review-status">Decision</label>
                        <select id="review-status" name="status">
                            <option value="approved"<?= (($galleryReviewForm['status'] ?? '') === 'approved') ? ' selected' : '' ?>>Approve</option>
                            <option value="rejected"<?= (($galleryReviewForm['status'] ?? '') === 'rejected') ? ' selected' : '' ?>>Reject</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="review-notes">Review Notes</label>
                        <textarea id="review-notes" name="review_notes" rows="4"><?= e((string) ($galleryReviewForm['review_notes'] ?? '')) ?></textarea>
                    </div>
                </div>
            </section>

            <div class="admin-form-actions">
                <a class="admin-secondary-action" href="<?= e(route_url('/admin/submissions/gallery')) ?>">Cancel</a>
                <button class="admin-primary-action" type="submit">Save Review</button>
            </div>
        </form>
    </div>
</section>
