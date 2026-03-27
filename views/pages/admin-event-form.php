<?php
declare(strict_types=1);

$eventState = is_array($eventState ?? null) ? $eventState : [];
$eventForm = is_array($eventForm ?? null) ? $eventForm : [];
$eventFormMode = (string) ($eventFormMode ?? 'create');
$eventId = isset($eventId) ? (int) $eventId : null;
$formAction = $eventFormMode === 'edit' && $eventId !== null
    ? route_url('/admin/events/' . $eventId . '/edit')
    : route_url('/admin/events/new');
?>
<section class="admin-dashboard admin-dashboard--event-form">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Events / <?= e($eventFormMode === 'edit' ? 'Edit Event' : 'Add New Event') ?></p>
            <h1><?= e($eventFormMode === 'edit' ? 'Edit Event' : 'Add New Event') ?></h1>
            <p class="admin-dashboard__intro">Use the real event fields below so the public event list and detail pages stay in sync.</p>
        </div>

        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/events')) ?>">Back to Events</a>
        </div>
    </header>

    <?php if (($eventState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($eventState['type'] ?? 'info') ?>">
            <?= e($eventState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e($formAction) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_input() ?>

        <div class="admin-events-form__grid">
            <section class="admin-form-panel admin-form-panel--primary">
                <div class="admin-field">
                    <label for="event-title">Event Title</label>
                    <input id="event-title" name="title" type="text" value="<?= e((string) ($eventForm['title'] ?? '')) ?>" required>
                </div>

                <div class="admin-form-panel__two-up">
                    <div class="admin-field">
                        <label for="event-slug">Slug</label>
                        <input id="event-slug" name="slug" type="text" value="<?= e((string) ($eventForm['slug'] ?? '')) ?>" placeholder="annual-brahmotsavam-celebrations" required>
                    </div>

                    <div class="admin-field">
                        <label for="event-schedule-label">Schedule Label</label>
                        <input id="event-schedule-label" name="schedule_label" type="text" value="<?= e((string) ($eventForm['schedule_label'] ?? '')) ?>" placeholder="Grand Temple Celebration">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="event-summary">Short Description</label>
                    <textarea id="event-summary" name="summary" rows="4" required><?= e((string) ($eventForm['summary'] ?? '')) ?></textarea>
                </div>

                <div class="admin-field">
                    <label for="event-body-long">Full Description</label>
                    <textarea id="event-body-long" name="body_long" rows="12" required><?= e((string) ($eventForm['body_long'] ?? '')) ?></textarea>
                </div>
            </section>

            <div class="admin-events-form__sidebar">
                <section class="admin-form-panel">
                    <h2>Schedule</h2>

                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="event-starts-at">Starts At</label>
                            <input id="event-starts-at" name="starts_at" type="datetime-local" value="<?= e((string) ($eventForm['starts_at'] ?? '')) ?>" required>
                        </div>

                        <div class="admin-field">
                            <label for="event-ends-at">Ends At</label>
                            <input id="event-ends-at" name="ends_at" type="datetime-local" value="<?= e((string) ($eventForm['ends_at'] ?? '')) ?>">
                        </div>
                    </div>
                </section>

                <section class="admin-form-panel">
                    <h2>Publishing</h2>

                    <div class="admin-field">
                        <label for="event-status">Status</label>
                        <select id="event-status" name="status">
                            <option value="draft"<?= (string) ($eventForm['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft</option>
                            <option value="published"<?= (string) ($eventForm['status'] ?? '') === 'published' ? ' selected' : '' ?>>Published</option>
                        </select>
                    </div>

                    <div class="admin-field">
                        <label for="event-sort-order">Sort Order</label>
                        <input id="event-sort-order" name="sort_order" type="number" min="0" step="1" value="<?= e((string) ($eventForm['sort_order'] ?? '0')) ?>">
                    </div>

                    <label class="admin-checkbox">
                        <input name="is_featured_home" type="checkbox" value="1"<?= ! empty($eventForm['is_featured_home']) ? ' checked' : '' ?>>
                        <span>Feature this event on the Home page</span>
                    </label>
                </section>

                <section class="admin-form-panel">
                    <h2>Image Asset</h2>

                    <?php
                    $mediaFieldName = 'image_path';
                    $mediaFieldId = 'event-image';
                    $mediaFieldLabel = 'Event Image';
                    $mediaCurrentPath = (string) ($eventForm['image_path'] ?? '');
                    $mediaSelectName = 'image_media_id';
                    $mediaUploadName = 'image_upload';
                    $mediaRequired = false;
                    $mediaHelp = 'Select an existing event image or upload a new one. Leaving both untouched keeps the current image.';
                    $mediaPreviewAlt = (string) (($eventForm['title'] ?? 'Event') . ' image preview');
                    require __DIR__ . '/../partials/admin-media-field.php';
                    ?>
                </section>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/events')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit"><?= e($eventFormMode === 'edit' ? 'Save Event' : 'Create Event') ?></button>
        </div>
    </form>
</section>
