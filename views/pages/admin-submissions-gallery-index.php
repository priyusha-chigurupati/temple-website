<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$gallerySubmissionItems = is_array($gallerySubmissionItems ?? null) ? $gallerySubmissionItems : [];
$gallerySubmissionFilters = is_array($gallerySubmissionFilters ?? null) ? $gallerySubmissionFilters : [];
$gallerySubmissionPagination = is_array($gallerySubmissionPagination ?? null) ? $gallerySubmissionPagination : [];
$gallerySubmissionStatus = (string) ($gallerySubmissionStatus ?? 'all');
$gallerySubmissionSearchTerm = (string) ($gallerySubmissionSearchTerm ?? '');
$gallerySubmissionTotalItems = (int) ($gallerySubmissionTotalItems ?? count($gallerySubmissionItems));
$basePath = route_url('/admin/submissions/gallery');
$buildUrl = static function (int $page) use ($basePath, $gallerySubmissionStatus, $gallerySubmissionSearchTerm): string {
    $params = ['page' => $page];
    if ($gallerySubmissionStatus !== 'all') {
        $params['status'] = $gallerySubmissionStatus;
    }
    if ($gallerySubmissionSearchTerm !== '') {
        $params['q'] = $gallerySubmissionSearchTerm;
    }
    return $basePath . '?' . http_build_query($params);
};
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center / Gallery</p>
            <h1>Gallery Submissions</h1>
            <p class="admin-dashboard__intro">Review public photo submissions and decide whether to approve or reject them for later publishing.</p>
        </div>
        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/submissions')) ?>">Back to Submission Center</a>
        </div>
    </header>

    <?php if (($submissionState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($submissionState['type'] ?? 'info') ?>">
            <?= e($submissionState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="admin-panel admin-panel--events-list">
        <div class="admin-panel__header admin-panel__header--inline admin-events-panel__header">
            <div>
                <h2>Submission Listing</h2>
                <p class="admin-events-panel__subcopy">These photo submissions come from the public Gallery page and stay hidden until reviewed.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) $gallerySubmissionTotalItems) ?> records</span>
        </div>

        <div class="admin-events-toolbar">
            <div class="admin-events-toolbar__filters">
                <?php foreach ($gallerySubmissionFilters as $filter): ?>
                    <a class="admin-events-filter<?= (($filter['key'] ?? 'all') === $gallerySubmissionStatus) ? ' admin-events-filter--active' : '' ?>" href="<?= e($basePath . (($filter['key'] ?? 'all') === 'all' ? '' : ('?status=' . urlencode((string) $filter['key'])))) ?>">
                        <?= e(($filter['label'] ?? '') . ' (' . (string) ($filter['count'] ?? 0) . ')') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="admin-events-search admin-events-search--standalone" action="<?= e($basePath) ?>" method="get">
            <?php if ($gallerySubmissionStatus !== 'all'): ?>
                <input name="status" type="hidden" value="<?= e($gallerySubmissionStatus) ?>">
            <?php endif; ?>
            <input name="q" type="search" value="<?= e($gallerySubmissionSearchTerm) ?>" placeholder="Search gallery submissions...">
            <button type="submit">Search</button>
        </form>

        <?php if ($gallerySubmissionItems === []): ?>
            <p class="admin-panel__empty">No gallery submissions match the current filters.</p>
        <?php else: ?>
            <div class="admin-submission-list">
                <?php foreach ($gallerySubmissionItems as $item): ?>
                    <article class="admin-submission-row">
                        <div class="admin-submission-row__main">
                            <h3><?= e((string) ($item['submitter_name'] ?? '')) ?></h3>
                            <p><?= e((string) ($item['submitter_email'] ?? '')) ?></p>
                        </div>
                        <div class="admin-submission-row__meta">
                            <span><?= e((string) ($item['file_count'] ?? 0)) ?> files</span>
                            <small><?= e(date('M d, Y h:i A', strtotime((string) ($item['created_at'] ?? 'now')))) ?></small>
                        </div>
                        <div class="admin-submission-row__status">
                            <span class="admin-events-phase admin-events-phase--<?= e((string) ($item['status'] ?? 'pending')) ?>">
                                <?= e(ucfirst((string) ($item['status'] ?? 'pending'))) ?>
                            </span>
                        </div>
                        <div class="admin-submission-row__actions">
                            <a href="<?= e(route_url('/admin/submissions/gallery/' . (string) ($item['id'] ?? 0))) ?>">Review</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (($gallerySubmissionPagination['total_pages'] ?? 1) > 1): ?>
            <div class="admin-events-pagination">
                <?php for ($page = 1; $page <= (int) ($gallerySubmissionPagination['total_pages'] ?? 1); $page++): ?>
                    <a class="admin-events-pagination__page<?= $page === (int) ($gallerySubmissionPagination['page'] ?? 1) ? ' admin-events-pagination__page--active' : '' ?>" href="<?= e($buildUrl($page)) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
