<?php
declare(strict_types=1);

$blogState = is_array($blogState ?? null) ? $blogState : [];
$blogForm = is_array($blogForm ?? null) ? $blogForm : [];
$blogCategories = is_array($blogCategories ?? null) ? $blogCategories : [];
$blogFormMode = (string) ($blogFormMode ?? 'create');
$blogPostId = isset($blogPostId) ? (int) $blogPostId : null;
$formAction = $blogFormMode === 'edit' && $blogPostId !== null
    ? route_url('/admin/blog/' . $blogPostId . '/edit')
    : route_url('/admin/blog/new');
?>
<section class="admin-dashboard admin-dashboard--blog-form">
    <header class="admin-dashboard__topbar">
        <div>
            <p class="admin-dashboard__crumbs">Admin / Blog / <?= e($blogFormMode === 'edit' ? 'Edit Article' : 'Add Article') ?></p>
            <h1><?= e($blogFormMode === 'edit' ? 'Edit Blog Article' : 'Add Blog Article') ?></h1>
            <p class="admin-dashboard__intro">Write and structure articles with the same fields the public Blog page already reads from the database.</p>
        </div>

        <div class="admin-dashboard__actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/blog')) ?>">Back to Blog</a>
        </div>
    </header>

    <?php if (($blogState['message'] ?? '') !== ''): ?>
        <div class="admin-form-state admin-form-state--<?= e($blogState['type'] ?? 'info') ?>">
            <?= e($blogState['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="admin-events-form" action="<?= e($formAction) ?>" method="post">
        <?= csrf_input() ?>

        <div class="admin-events-form__grid">
            <section class="admin-form-panel admin-form-panel--primary">
                <div class="admin-field">
                    <label for="blog-title">Article Title</label>
                    <input id="blog-title" name="title" type="text" value="<?= e((string) ($blogForm['title'] ?? '')) ?>" required>
                </div>

                <div class="admin-field">
                    <label for="blog-slug">Slug</label>
                    <input id="blog-slug" name="slug" type="text" value="<?= e((string) ($blogForm['slug'] ?? '')) ?>" placeholder="sacred-reflections-at-dawn">
                    <small>If left blank, the slug will be generated from the title.</small>
                </div>

                <div class="admin-field">
                    <label for="blog-excerpt">Excerpt</label>
                    <textarea id="blog-excerpt" name="excerpt" rows="4" required><?= e((string) ($blogForm['excerpt'] ?? '')) ?></textarea>
                </div>

                <div class="admin-field">
                    <label for="blog-body">Article Body</label>
                    <textarea id="blog-body" name="body_long" rows="12" required><?= e((string) ($blogForm['body_long'] ?? '')) ?></textarea>
                </div>
            </section>

            <div class="admin-events-form__sidebar">
                <section class="admin-form-panel">
                    <h2>Publishing</h2>

                    <div class="admin-field">
                        <label for="blog-category">Category</label>
                        <select id="blog-category" name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($blogCategories as $category): ?>
                                <option value="<?= e((string) ($category['id'] ?? '')) ?>"<?= (string) ($blogForm['category_id'] ?? '') === (string) ($category['id'] ?? '') ? ' selected' : '' ?>>
                                    <?= e((string) ($category['name'] ?? 'Category')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-field">
                        <label for="blog-image-path">Featured Image Path</label>
                        <input id="blog-image-path" name="image_path" type="text" value="<?= e((string) ($blogForm['image_path'] ?? '')) ?>" placeholder="assets/images/placeholders/blog-post-deepam.svg" required>
                    </div>

                    <div class="admin-form-panel__two-up">
                        <div class="admin-field">
                            <label for="blog-status">Status</label>
                            <select id="blog-status" name="status">
                                <option value="draft"<?= (string) ($blogForm['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft</option>
                                <option value="published"<?= (string) ($blogForm['status'] ?? '') === 'published' ? ' selected' : '' ?>>Published</option>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="blog-read-time">Read Time</label>
                            <input id="blog-read-time" name="read_time_label" type="text" value="<?= e((string) ($blogForm['read_time_label'] ?? '6 Min Read')) ?>" placeholder="6 Min Read">
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="blog-published-at">Publish Date</label>
                        <input id="blog-published-at" name="published_at" type="datetime-local" value="<?= e((string) ($blogForm['published_at'] ?? '')) ?>">
                    </div>

                    <label class="admin-checkbox">
                        <input name="is_featured" type="checkbox" value="1"<?= ! empty($blogForm['is_featured']) ? ' checked' : '' ?>>
                        <span>Mark as the featured article</span>
                    </label>
                </section>

                <section class="admin-form-panel">
                    <h2>SEO Details</h2>

                    <div class="admin-field">
                        <label for="blog-meta-title">Meta Title</label>
                        <input id="blog-meta-title" name="meta_title" type="text" value="<?= e((string) ($blogForm['meta_title'] ?? '')) ?>">
                    </div>

                    <div class="admin-field">
                        <label for="blog-meta-description">Meta Description</label>
                        <textarea id="blog-meta-description" name="meta_description" rows="4"><?= e((string) ($blogForm['meta_description'] ?? '')) ?></textarea>
                    </div>
                </section>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="admin-secondary-action" href="<?= e(route_url('/admin/blog')) ?>">Cancel</a>
            <button class="admin-primary-action" type="submit"><?= e($blogFormMode === 'edit' ? 'Save Article' : 'Create Article') ?></button>
        </div>
    </form>
</section>
