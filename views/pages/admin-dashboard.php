<?php
declare(strict_types=1);
?>
<section class="admin-dashboard">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Dashboard Overview</p>
            <h1>Dashboard Overview</h1>
            <p class="admin-dashboard__intro">Welcome back, here is what is happening across the temple website today.</p>
        </div>

        <div class="admin-dashboard__actions">
            <button class="admin-icon-button" type="button" aria-label="Notifications" title="Notifications">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <button class="admin-primary-action" type="button">+ Quick Action</button>
        </div>
    </header>

    <section class="admin-overview-grid">
        <article class="admin-overview-card">
            <div class="admin-overview-card__icon">
                <span class="material-symbols-outlined">auto_stories</span>
            </div>
            <div class="admin-overview-card__meta">Total Content</div>
            <strong><?= e((string) ($totalContentCount ?? 0)) ?></strong>
            <p><?= e((string) ($contentCounts['pages'] ?? 0)) ?> pages, <?= e((string) ($contentCounts['events'] ?? 0)) ?> events, <?= e((string) ($contentCounts['gallery_items'] ?? 0)) ?> gallery items, <?= e((string) ($contentCounts['blog_posts'] ?? 0)) ?> posts.</p>
        </article>

        <article class="admin-overview-card admin-overview-card--highlight">
            <div class="admin-overview-card__meta">Donation Notices</div>
            <strong>Rs <?= e(number_format((float) ($donationSummary['total_amount'] ?? 0), 0)) ?></strong>
            <p><?= e((string) ($donationSummary['total_records'] ?? 0)) ?> notices recorded in the last 30 days.</p>
            <div class="admin-overview-card__bar">
                <?php
                $donationCount = (int) ($donationSummary['total_records'] ?? 0);
                $progressWidth = min(100, $donationCount * 18);
                ?>
                <span style="width: <?= e((string) $progressWidth) ?>%"></span>
            </div>
        </article>

        <article class="admin-overview-card admin-overview-card--status">
            <div class="admin-overview-card__icon admin-overview-card__icon--secondary">
                <span class="material-symbols-outlined">schedule</span>
            </div>
            <div class="admin-overview-card__meta">Active Status</div>
            <strong><?= e($nextEventStatus['title'] ?? 'No Status') ?></strong>
            <p><?= e($nextEventStatus['detail'] ?? 'No upcoming schedule yet.') ?></p>
        </article>
    </section>

    <section class="admin-dashboard__content">
        <article class="admin-panel admin-panel--events">
            <div class="admin-panel__header admin-panel__header--inline">
                <h2>Upcoming Events</h2>
                <span class="admin-panel__link">View All</span>
            </div>

            <?php if (($upcomingEvents ?? []) === []): ?>
                <p class="admin-panel__empty">No upcoming events are available yet.</p>
            <?php else: ?>
                <div class="admin-event-list">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <article class="admin-event-item">
                            <div class="admin-event-item__image">
                                <?php if (($event['image'] ?? '') !== ''): ?>
                                    <img src="<?= e(asset($event['image'])) ?>" alt="<?= e($event['title'] ?? 'Event image') ?>">
                                <?php else: ?>
                                    <div class="admin-event-item__image-placeholder"></div>
                                <?php endif; ?>
                            </div>
                            <div class="admin-event-item__content">
                                <h3><?= e($event['title'] ?? '') ?></h3>
                                <p><?= e($event['date'] ?? '') ?></p>
                            </div>
                            <div class="admin-event-item__badge">
                                <span><?= e($event['status'] ?? 'Published') ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="admin-panel admin-panel--donations">
            <div class="admin-panel__header admin-panel__header--inline">
                <h2>Recent Donation Notices</h2>
                <span class="admin-panel__link">Latest Records</span>
            </div>

            <?php if ($recentDonationNotifications === []): ?>
                <p class="admin-panel__empty">No donation notices have been submitted yet.</p>
            <?php else: ?>
                <div class="admin-donation-list">
                    <?php foreach ($recentDonationNotifications as $notice): ?>
                        <article class="admin-donation-item">
                            <div class="admin-donation-item__avatar">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div class="admin-donation-item__content">
                                <h3><?= e($notice['full_name'] ?? '') ?></h3>
                                <p><?= e($notice['payment_method'] ?? '') ?> • <?= e((string) ($notice['created_at'] ?? '')) ?></p>
                            </div>
                            <div class="admin-donation-item__amount">
                                <strong>Rs <?= e(number_format((float) ($notice['amount'] ?? 0), 0)) ?></strong>
                                <span><?= e(ucfirst((string) ($notice['status'] ?? 'pending'))) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    </section>

    <section class="admin-dashboard__summary">
        <article class="admin-summary-card">
            <h2>Submission Inbox</h2>
            <p>Pending contact messages: <strong><?= e((string) ($submissionCounts['contact_pending'] ?? 0)) ?></strong></p>
            <p>Pending donation notices: <strong><?= e((string) ($submissionCounts['donation_pending'] ?? 0)) ?></strong></p>
        </article>

        <article class="admin-summary-card">
            <h2>Content Snapshot</h2>
            <p>Published pages: <strong><?= e((string) ($contentCounts['pages'] ?? 0)) ?></strong></p>
            <p>Gallery items: <strong><?= e((string) ($contentCounts['gallery_items'] ?? 0)) ?></strong></p>
        </article>
    </section>
</section>
