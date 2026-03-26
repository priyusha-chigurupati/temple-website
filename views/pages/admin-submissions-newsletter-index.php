<?php
declare(strict_types=1);

$submissionState = is_array($submissionState ?? null) ? $submissionState : [];
$newsletterItems = is_array($newsletterItems ?? null) ? $newsletterItems : [];
$newsletterFilters = is_array($newsletterFilters ?? null) ? $newsletterFilters : [];
$newsletterPagination = is_array($newsletterPagination ?? null) ? $newsletterPagination : [];
$newsletterStatus = (string) ($newsletterStatus ?? 'all');
$newsletterSearchTerm = (string) ($newsletterSearchTerm ?? '');
$newsletterTotalItems = (int) ($newsletterTotalItems ?? count($newsletterItems));
$basePath = route_url('/admin/submissions/newsletter');
$buildUrl = static function (int $page) use ($basePath, $newsletterStatus, $newsletterSearchTerm): string {
    $params = ['page' => $page];
    if ($newsletterStatus !== 'all') {
        $params['status'] = $newsletterStatus;
    }
    if ($newsletterSearchTerm !== '') {
        $params['q'] = $newsletterSearchTerm;
    }
    return $basePath . '?' . http_build_query($params);
};
?>
<section class="admin-dashboard admin-dashboard--submissions">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Submission Center / Newsletter</p>
            <h1>Newsletter Subscribers</h1>
            <p class="admin-dashboard__intro">Review the subscriber list collected from the public site and manage subscription status.</p>
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
                <h2>Subscriber Listing</h2>
                <p class="admin-events-panel__subcopy">These email addresses were collected from newsletter forms on the public website.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) $newsletterTotalItems) ?> records</span>
        </div>

        <div class="admin-events-toolbar">
            <div class="admin-events-toolbar__filters">
                <?php foreach ($newsletterFilters as $filter): ?>
                    <a class="admin-events-filter<?= (($filter['key'] ?? 'all') === $newsletterStatus) ? ' admin-events-filter--active' : '' ?>" href="<?= e($basePath . (($filter['key'] ?? 'all') === 'all' ? '' : ('?status=' . urlencode((string) $filter['key'])))) ?>">
                        <?= e(($filter['label'] ?? '') . ' (' . (string) ($filter['count'] ?? 0) . ')') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="admin-events-search admin-events-search--standalone" action="<?= e($basePath) ?>" method="get">
            <?php if ($newsletterStatus !== 'all'): ?>
                <input name="status" type="hidden" value="<?= e($newsletterStatus) ?>">
            <?php endif; ?>
            <input name="q" type="search" value="<?= e($newsletterSearchTerm) ?>" placeholder="Search newsletter subscribers...">
            <button type="submit">Search</button>
        </form>

        <?php if ($newsletterItems === []): ?>
            <p class="admin-panel__empty">No newsletter subscriptions match the current filters.</p>
        <?php else: ?>
            <div class="admin-submission-list">
                <?php foreach ($newsletterItems as $item): ?>
                    <article class="admin-submission-row">
                        <div class="admin-submission-row__main">
                            <h3><?= e((string) ($item['email'] ?? '')) ?></h3>
                            <p><?= e((string) ($item['source'] ?? 'public-site')) ?></p>
                        </div>
                        <div class="admin-submission-row__meta">
                            <span><?= e(date('M d, Y h:i A', strtotime((string) ($item['created_at'] ?? 'now')))) ?></span>
                        </div>
                        <div class="admin-submission-row__status">
                            <span class="admin-events-phase admin-events-phase--<?= e((string) ($item['status'] ?? 'active')) ?>">
                                <?= e(ucfirst((string) ($item['status'] ?? 'active'))) ?>
                            </span>
                        </div>
                        <div class="admin-submission-row__actions">
                            <?php if (($item['status'] ?? 'active') === 'active'): ?>
                                <form action="<?= e(route_url('/admin/submissions/newsletter/' . (string) ($item['id'] ?? 0) . '/unsubscribe')) ?>" method="post">
                                    <?= csrf_input() ?>
                                    <button type="submit">Unsubscribe</button>
                                </form>
                            <?php else: ?>
                                <form action="<?= e(route_url('/admin/submissions/newsletter/' . (string) ($item['id'] ?? 0) . '/activate')) ?>" method="post">
                                    <?= csrf_input() ?>
                                    <button type="submit">Activate</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (($newsletterPagination['total_pages'] ?? 1) > 1): ?>
            <div class="admin-events-pagination">
                <?php for ($page = 1; $page <= (int) ($newsletterPagination['total_pages'] ?? 1); $page++): ?>
                    <a class="admin-events-pagination__page<?= $page === (int) ($newsletterPagination['page'] ?? 1) ? ' admin-events-pagination__page--active' : '' ?>" href="<?= e($buildUrl($page)) ?>"><?= e((string) $page) ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
