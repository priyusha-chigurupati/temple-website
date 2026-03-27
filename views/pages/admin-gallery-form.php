<?php
declare(strict_types=1);

$galleryState = is_array($galleryState ?? null) ? $galleryState : [];
$galleryForm = is_array($galleryForm ?? null) ? $galleryForm : [];
$galleryCategories = is_array($galleryCategories ?? null) ? $galleryCategories : [];
$galleryFormMode = (string) ($galleryFormMode ?? 'create');
$galleryItemId = isset($galleryItemId) ? (int) $galleryItemId : null;
$formAction = $galleryFormMode === 'edit' && $galleryItemId !== null
    ? route_url('/admin/media/' . $galleryItemId . '/edit')
    : route_url('/admin/media/new');
?>
<section class="admin-dashboard admin-dashboard--gallery-form">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Media / <?= e($galleryFormMode === 'edit' ? 'Edit Item' : 'Add Media Item') ?></p>
            <h1><?= e($galleryFormMode === 'edit' ? 'Edit Media Item' : 'Add Media Item') ?></h1>
            <p class="admin-dashboard__intro">Curate gallery visuals with the same data fields the public Gallery page already uses.</p>
        </div>

        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/media')) ?>">Back to Media</a>
        </div>
    </header>

    <?php if (($galleryState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($galleryState['type'] ?? 'info') ?>">
            <?= e($galleryState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e($formAction) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_input() ?>

        <div class="admin-events-form__grid">
            <section class="admin-form-panel admin-form-panel--primary">
                <div class="admin-field">
                    <label for="gallery-title">Gallery Title</label>
                    <input id="gallery-title" name="title" type="text" value="<?= e((string) ($galleryForm['title'] ?? '')) ?>" required>
                </div>

                <div class="admin-field">
                    <label for="gallery-caption">Caption</label>
                    <textarea id="gallery-caption" name="caption" rows="5"><?= e((string) ($galleryForm['caption'] ?? '')) ?></textarea>
                </div>

                <?php
                $mediaFieldName = 'image_path';
                $mediaFieldId = 'gallery-image';
                $mediaFieldLabel = 'Gallery Image';
                $mediaCurrentPath = (string) ($galleryForm['image_path'] ?? '');
                $mediaSelectName = 'image_media_id';
                $mediaUploadName = 'image_upload';
                $mediaRequired = true;
                $mediaHelp = 'Upload a new gallery image or reuse one from the media library. If this is an existing item, leaving both untouched keeps the current image.';
                $mediaPreviewAlt = (string) (($galleryForm['title'] ?? 'Gallery') . ' image preview');
                require __DIR__ . '/../partials/admin-media-field.php';
                ?>
            </section>

            <div class="admin-events-form__sidebar">
                <section class="admin-form-panel">
                    <h2>Category & Status</h2>

                    <div class="admin-field">
                        <label for="gallery-category">Category</label>
                        <select id="gallery-category" name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($galleryCategories as $category): ?>
                                <option value="<?= e((string) ($category['id'] ?? '')) ?>"<?= (string) ($galleryForm['category_id'] ?? '') === (string) ($category['id'] ?? '') ? ' selected' : '' ?>>
                                    <?= e((string) ($category['name'] ?? 'Category')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="gallery-status">Status</label>
                            <select id="gallery-status" name="status">
                                <option value="draft"<?= (string) ($galleryForm['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft</option>
                                <option value="published"<?= (string) ($galleryForm['status'] ?? '') === 'published' ? ' selected' : '' ?>>Published</option>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="gallery-sort-order">Sort Order</label>
                            <input id="gallery-sort-order" name="sort_order" type="number" min="0" step="1" value="<?= e((string) ($galleryForm['sort_order'] ?? '0')) ?>">
                        </div>
                    </div>
                </section>

                <section class="admin-form-panel">
                    <h2>Placement</h2>

                    <label class="admin-checkbox">
                        <input name="is_featured" type="checkbox" value="1"<?= ! empty($galleryForm['is_featured']) ? ' checked' : '' ?>>
                        <span>Mark as a featured gallery item</span>
                    </label>

                    <label class="admin-checkbox">
                        <input name="is_featured_home" type="checkbox" value="1"<?= ! empty($galleryForm['is_featured_home']) ? ' checked' : '' ?>>
                        <span>Show on the Home page highlights</span>
                    </label>
                </section>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/media')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit"><?= e($galleryFormMode === 'edit' ? 'Save Item' : 'Create Item') ?></button>
        </div>
    </form>
</section>
