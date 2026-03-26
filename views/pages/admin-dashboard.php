<?php
declare(strict_types=1);
?>
<section class="admin-dashboard">
    <header class="admin-topbar">
        <div>
            <p class="admin-topbar__eyebrow">AnkammaThalli Temple</p>
            <h1>Admin Dashboard</h1>
            <p class="admin-topbar__welcome">Signed in as <?= e($adminUser['name'] ?? 'Admin') ?>.</p>
        </div>

        <form action="<?= e(route_url('/admin/logout')) ?>" method="post">
            <?= csrf_input() ?>
            <button class="admin-topbar__logout" type="submit">Log Out</button>
        </form>
    </header>

    <section class="admin-metrics">
        <article class="admin-metric-card">
            <span>Published Pages</span>
            <strong><?= e((string) ($contentCounts['pages'] ?? 0)) ?></strong>
        </article>
        <article class="admin-metric-card">
            <span>Published Events</span>
            <strong><?= e((string) ($contentCounts['events'] ?? 0)) ?></strong>
        </article>
        <article class="admin-metric-card">
            <span>Gallery Items</span>
            <strong><?= e((string) ($contentCounts['gallery_items'] ?? 0)) ?></strong>
        </article>
        <article class="admin-metric-card">
            <span>Blog Posts</span>
            <strong><?= e((string) ($contentCounts['blog_posts'] ?? 0)) ?></strong>
        </article>
        <article class="admin-metric-card admin-metric-card--alert">
            <span>Pending Contact Messages</span>
            <strong><?= e((string) ($submissionCounts['contact_pending'] ?? 0)) ?></strong>
        </article>
        <article class="admin-metric-card admin-metric-card--alert">
            <span>Pending Donation Notices</span>
            <strong><?= e((string) ($submissionCounts['donation_pending'] ?? 0)) ?></strong>
        </article>
    </section>

    <section class="admin-panels">
        <article class="admin-panel">
            <div class="admin-panel__header">
                <h2>Recent Contact Messages</h2>
                <p>Latest inquiries captured from the public Contact form.</p>
            </div>

            <?php if ($recentContactInquiries === []): ?>
                <p class="admin-panel__empty">No contact messages have been submitted yet.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentContactInquiries as $inquiry): ?>
                                <tr>
                                    <td><?= e($inquiry['full_name'] ?? '') ?></td>
                                    <td><?= e($inquiry['email'] ?? '') ?></td>
                                    <td><?= e($inquiry['subject'] ?? '') ?></td>
                                    <td><?= e(ucfirst((string) ($inquiry['status'] ?? 'pending'))) ?></td>
                                    <td><?= e((string) ($inquiry['created_at'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>

        <article class="admin-panel">
            <div class="admin-panel__header">
                <h2>Recent Donation Notices</h2>
                <p>Latest donation notifications submitted after offline, UPI, or bank contributions.</p>
            </div>

            <?php if ($recentDonationNotifications === []): ?>
                <p class="admin-panel__empty">No donation notices have been submitted yet.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDonationNotifications as $notice): ?>
                                <tr>
                                    <td><?= e($notice['full_name'] ?? '') ?></td>
                                    <td><?= e($notice['payment_method'] ?? '') ?></td>
                                    <td><?= e((string) ($notice['amount'] ?? '')) ?></td>
                                    <td><?= e(ucfirst((string) ($notice['status'] ?? 'pending'))) ?></td>
                                    <td><?= e((string) ($notice['created_at'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>
    </section>
</section>
