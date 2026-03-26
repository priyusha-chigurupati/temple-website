<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$contactItems = is_array($contactItems ?? null) ? $contactItems : [];
$contactFilters = is_array($contactFilters ?? null) ? $contactFilters : [];
$contactPagination = is_array($contactPagination ?? null) ? $contactPagination : [];
$contactStatus = (string) ($contactStatus ?? 'all');
$contactSearchTerm = (string) ($contactSearchTerm ?? '');
$contactTotalItems = (int) ($contactTotalItems ?? count($contactItems));
$basePath = route_url('/admin/submissions/contact');
$buildUrl = static function (int $page) use ($basePath, $contactStatus, $contactSearchTerm): string {
    $params = ['page' => $page];
    if ($contactStatus !== 'all') {
        $params['status'] = $contactStatus;
    }
    if ($contactSearchTerm !== '') {
        $params['q'] = $contactSearchTerm;
    }
    return $basePath . '?' . http_build_query($params);
};
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center / Contact</p>
            <h1>Contact Inquiries</h1>
            <p class="admin-dashboard__intro">Review and acknowledge messages submitted through the public Contact page.</p>
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
                <h2>Inquiry Listing</h2>
                <p class="admin-events-panel__subcopy">Messages below come directly from the public site contact form.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) $contactTotalItems) ?> records</span>
        </div>

        <div class="admin-events-toolbar">
            <div class="admin-events-toolbar__filters">
                <?php foreach ($contactFilters as $filter): ?>
                    <a class="admin-events-filter<?= (($filter['key'] ?? 'all') === $contactStatus) ? ' admin-events-filter--active' : '' ?>" href="<?= e($basePath . (($filter['key'] ?? 'all') === 'all' ? '' : ('?status=' . urlencode((string) $filter['key'])))) ?>">
                        <?= e(($filter['label'] ?? '') . ' (' . (string) ($filter['count'] ?? 0) . ')') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="admin-events-search admin-events-search--standalone" action="<?= e($basePath) ?>" method="get">
            <?php if ($contactStatus !== 'all'): ?>
                <input name="status" type="hidden" value="<?= e($contactStatus) ?>">
            <?php endif; ?>
            <input name="q" type="search" value="<?= e($contactSearchTerm) ?>" placeholder="Search contact inquiries...">
            <button type="submit">Search</button>
        </form>

        <?php if ($contactItems === []): ?>
            <p class="admin-panel__empty">No contact inquiries match the current filters.</p>
        <?php else: ?>
            <div class="admin-submission-list">
                <?php foreach ($contactItems as $item): ?>
                    <article class="admin-submission-row">
                        <div class="admin-submission-row__main">
                            <h3><?= e((string) ($item['full_name'] ?? '')) ?></h3>
                            <p><?= e((string) ($item['subject'] ?? '')) ?></p>
                        </div>
                        <div class="admin-submission-row__meta">
                            <span><?= e((string) ($item['email'] ?? '')) ?></span>
                            <small><?= e(date('M d, Y h:i A', strtotime((string) ($item['created_at'] ?? 'now')))) ?></small>
                        </div>
                        <div class="admin-submission-row__status">
                            <span class="admin-events-phase admin-events-phase--<?= e((string) ($item['status'] ?? 'pending')) ?>">
                                <?= e(ucfirst((string) ($item['status'] ?? 'pending'))) ?>
                            </span>
                        </div>
                        <div class="admin-submission-row__actions">
                            <a href="<?= e(route_url('/admin/submissions/contact/' . (string) ($item['id'] ?? 0))) ?>">View</a>
                            <?php if (($item['status'] ?? 'pending') !== 'reviewed'): ?>
                                <form action="<?= e(route_url('/admin/submissions/contact/' . (string) ($item['id'] ?? 0) . '/review')) ?>" method="post">
                                    <?= csrf_input() ?>
                                    <button type="submit">Mark Reviewed</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (($contactPagination['total_pages'] ?? 1) > 1): ?>
            <div class="admin-events-pagination">
                <?php for ($page = 1; $page <= (int) ($contactPagination['total_pages'] ?? 1); $page++): ?>
                    <a class="admin-events-pagination__page<?= $page === (int) ($contactPagination['page'] ?? 1) ? ' admin-events-pagination__page--active' : '' ?>" href="<?= e($buildUrl($page)) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
