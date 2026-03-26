<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$donationItems = is_array($donationItems ?? null) ? $donationItems : [];
$donationFilters = is_array($donationFilters ?? null) ? $donationFilters : [];
$donationPagination = is_array($donationPagination ?? null) ? $donationPagination : [];
$donationStatus = (string) ($donationStatus ?? 'all');
$donationSearchTerm = (string) ($donationSearchTerm ?? '');
$donationTotalItems = (int) ($donationTotalItems ?? count($donationItems));
$basePath = route_url('/admin/submissions/donations');
$buildUrl = static function (int $page) use ($basePath, $donationStatus, $donationSearchTerm): string {
    $params = ['page' => $page];
    if ($donationStatus !== 'all') {
        $params['status'] = $donationStatus;
    }
    if ($donationSearchTerm !== '') {
        $params['q'] = $donationSearchTerm;
    }
    return $basePath . '?' . http_build_query($params);
};
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center / Donations</p>
            <h1>Donation Notices</h1>
            <p class="admin-dashboard__intro">Review the donation notices submitted through the public Donations page.</p>
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
                <h2>Donation Notice Listing</h2>
                <p class="admin-events-panel__subcopy">These notices come from devotees who reported a donation after paying offline, through UPI, or through bank transfer.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) $donationTotalItems) ?> records</span>
        </div>

        <div class="admin-events-toolbar">
            <div class="admin-events-toolbar__filters">
                <?php foreach ($donationFilters as $filter): ?>
                    <a class="admin-events-filter<?= (($filter['key'] ?? 'all') === $donationStatus) ? ' admin-events-filter--active' : '' ?>" href="<?= e($basePath . (($filter['key'] ?? 'all') === 'all' ? '' : ('?status=' . urlencode((string) $filter['key'])))) ?>">
                        <?= e(($filter['label'] ?? '') . ' (' . (string) ($filter['count'] ?? 0) . ')') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="admin-events-search admin-events-search--standalone" action="<?= e($basePath) ?>" method="get">
            <?php if ($donationStatus !== 'all'): ?>
                <input name="status" type="hidden" value="<?= e($donationStatus) ?>">
            <?php endif; ?>
            <input name="q" type="search" value="<?= e($donationSearchTerm) ?>" placeholder="Search donation notices...">
            <button type="submit">Search</button>
        </form>

        <?php if ($donationItems === []): ?>
            <p class="admin-panel__empty">No donation notices match the current filters.</p>
        <?php else: ?>
            <div class="admin-submission-list">
                <?php foreach ($donationItems as $item): ?>
                    <article class="admin-submission-row">
                        <div class="admin-submission-row__main">
                            <h3><?= e((string) ($item['full_name'] ?? '')) ?></h3>
                            <p><?= e((string) ($item['payment_method'] ?? 'Donation notice')) ?> &middot; Rs <?= e(number_format((float) ($item['amount'] ?? 0), 2)) ?></p>
                        </div>
                        <div class="admin-submission-row__meta">
                            <span><?= e((string) (($item['reference_id'] ?? '') !== '' ? $item['reference_id'] : 'No reference ID')) ?></span>
                            <small><?= e(date('M d, Y h:i A', strtotime((string) ($item['created_at'] ?? 'now')))) ?></small>
                        </div>
                        <div class="admin-submission-row__status">
                            <span class="admin-events-phase admin-events-phase--<?= e((string) ($item['status'] ?? 'pending')) ?>">
                                <?= e(ucfirst((string) ($item['status'] ?? 'pending'))) ?>
                            </span>
                        </div>
                        <div class="admin-submission-row__actions">
                            <a href="<?= e(route_url('/admin/submissions/donations/' . (string) ($item['id'] ?? 0))) ?>">View</a>
                            <?php if (($item['status'] ?? 'pending') !== 'reviewed'): ?>
                                <form action="<?= e(route_url('/admin/submissions/donations/' . (string) ($item['id'] ?? 0) . '/review')) ?>" method="post">
                                    <?= csrf_input() ?>
                                    <button type="submit">Mark Reviewed</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (($donationPagination['total_pages'] ?? 1) > 1): ?>
            <div class="admin-events-pagination">
                <?php for ($page = 1; $page <= (int) ($donationPagination['total_pages'] ?? 1); $page++): ?>
                    <a class="admin-events-pagination__page<?= $page === (int) ($donationPagination['page'] ?? 1) ? ' admin-events-pagination__page--active' : '' ?>" href="<?= e($buildUrl($page)) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
