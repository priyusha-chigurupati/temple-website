<?php
declare(strict_types=1);

$eventState = is_array($eventState ?? null) ? $eventState : [];
$events = is_array($events ?? null) ? $events : [];
$phaseCounts = is_array($phaseCounts ?? null) ? $phaseCounts : [];
$nextEvent = is_array($nextEvent ?? null) ? $nextEvent : null;
$eventFilters = is_array($eventFilters ?? null) ? $eventFilters : [];
$eventPagination = is_array($eventPagination ?? null) ? $eventPagination : [];
$eventSearchTerm = (string) ($eventSearchTerm ?? '');
$eventSelectedPhase = (string) ($eventSelectedPhase ?? 'all');
$eventTotalItems = (int) ($eventTotalItems ?? count($events));
?>
<section class="admin-dashboard admin-dashboard--events">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Events Management</p>
            <h1>Manage Events</h1>
            <p class="admin-dashboard__intro">Schedule, edit, and organize temple festivities, puja timings, and community gatherings.</p>
        </div>

        <div class="admin-dashboard__actions">
            <a class="admin-primary-action" href="<?= e(route_url('/admin/events/new')) ?>">+ Add New Event</a>
        </div>
    </header>

    <?php if (($eventState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($eventState['type'] ?? 'info') ?>">
            <?= e($eventState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="admin-events-stats">
        <article class="admin-events-stat admin-events-stat--wide">
            <div>
                <span class="admin-events-stat__label">Upcoming Events</span>
                <strong><?= e((string) ($phaseCounts['upcoming'] ?? 0)) ?></strong>
            </div>
            <div>
                <span class="admin-events-stat__label">Active Events</span>
                <strong><?= e((string) ($phaseCounts['active'] ?? 0)) ?></strong>
            </div>
            <div>
                <span class="admin-events-stat__label">Completed</span>
                <strong><?= e((string) ($phaseCounts['completed'] ?? 0)) ?></strong>
            </div>
        </article>

        <article class="admin-events-stat admin-events-stat--accent">
            <span class="admin-events-stat__label">Drafts</span>
            <strong><?= e((string) ($phaseCounts['draft'] ?? 0)) ?></strong>
            <p>Events that still need publishing before they appear on the public site.</p>
        </article>

        <article class="admin-events-stat">
            <span class="admin-events-stat__label">Next Scheduled Event</span>
            <strong><?= e($nextEvent['title'] ?? 'No upcoming event') ?></strong>
            <p><?= e($nextEvent['date_label'] ?? 'Schedule pending') ?></p>
        </article>
    </section>

    <section class="admin-panel admin-panel--events-list">
        <div class="admin-panel__header admin-panel__header--inline admin-events-panel__header">
            <div>
                <h2>Event Listing</h2>
                <p class="admin-events-panel__subcopy">All event records below are connected to the public Events page.</p>
            </div>
            <span class="admin-panel__link"><?= e((string) $eventTotalItems) ?> records</span>
        </div>

        <div class="admin-events-toolbar">
            <div class="admin-events-toolbar__filters">
                <?php foreach ($eventFilters as $filter): ?>
                    <a class="admin-events-filter<?= ! empty($filter['active']) ? ' admin-events-filter--active' : '' ?>" href="<?= e($filter['href'] ?? route_url('/admin/events')) ?>">
                        <?= e($filter['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="admin-events-search admin-events-search--standalone" action="<?= e(route_url('/admin/events')) ?>" method="get">
            <?php if ($eventSelectedPhase !== 'all'): ?>
                <input name="phase" type="hidden" value="<?= e($eventSelectedPhase) ?>">
            <?php endif; ?>
            <input name="q" type="search" value="<?= e($eventSearchTerm) ?>" placeholder="Search events...">
            <button type="submit">Search</button>
        </form>

        <?php if ($events === []): ?>
            <p class="admin-panel__empty">No events have been added yet. Use the button above to create the first one.</p>
        <?php else: ?>
            <div class="admin-events-table">
                <?php foreach ($events as $event): ?>
                    <article class="admin-events-row">
                        <div class="admin-events-row__image">
                            <?php if (($event['image_path'] ?? '') !== ''): ?>
                                <img src="<?= e(asset($event['image_path'])) ?>" alt="<?= e($event['title'] ?? 'Event image') ?>">
                            <?php else: ?>
                                <div class="admin-events-row__image-placeholder"></div>
                            <?php endif; ?>
                        </div>

                        <div class="admin-events-row__main">
                            <h3><?= e($event['title'] ?? '') ?></h3>
                            <p><?= e($event['schedule_label'] ?? 'Temple event') ?></p>
                        </div>

                        <div class="admin-events-row__meta">
                            <span><?= e($event['date_label'] ?? 'Schedule pending') ?></span>
                            <code>/events/<?= e($event['slug'] ?? '') ?></code>
                        </div>

                        <div class="admin-events-row__status">
                            <span class="admin-events-phase admin-events-phase--<?= e($event['phase'] ?? 'upcoming') ?>">
                                <?= e(ucfirst((string) ($event['phase'] ?? 'upcoming'))) ?>
                            </span>
                            <small><?= e(ucfirst((string) ($event['status'] ?? 'draft'))) ?></small>
                        </div>

                        <div class="admin-events-row__actions">
                            <?php if (($event['slug'] ?? '') !== ''): ?>
                                <a href="<?= e(route_url('/events/' . $event['slug'])) ?>" target="_blank" rel="noreferrer">View</a>
                            <?php endif; ?>
                            <a href="<?= e(route_url('/admin/events/' . (string) ($event['id'] ?? 0) . '/edit')) ?>">Edit</a>
                            <form action="<?= e(route_url('/admin/events/' . (string) ($event['id'] ?? 0) . '/delete')) ?>" method="post">
                                <?= csrf_input() ?>
                                <button type="submit" onclick="return confirm('Delete this event record?');">Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (($eventPagination['pages'] ?? []) !== [] && count($eventPagination['pages']) > 1): ?>
            <div class="admin-events-pagination">
                <?php if (($eventPagination['previous']['href'] ?? null) !== null): ?>
                    <a class="admin-events-pagination__arrow" href="<?= e($eventPagination['previous']['href']) ?>">Previous</a>
                <?php endif; ?>

                <?php foreach ($eventPagination['pages'] as $pageNumber): ?>
                    <a class="admin-events-pagination__page<?= ! empty($pageNumber['active']) ? ' admin-events-pagination__page--active' : '' ?>" href="<?= e($pageNumber['href'] ?? route_url('/admin/events')) ?>">
                        <?= e($pageNumber['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>

                <?php if (($eventPagination['next']['href'] ?? null) !== null): ?>
                    <a class="admin-events-pagination__arrow" href="<?= e($eventPagination['next']['href']) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
