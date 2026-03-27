<?php
declare(strict_types=1);

$mediaFieldName = (string) ($mediaFieldName ?? 'image_path');
$mediaFieldId = (string) ($mediaFieldId ?? preg_replace('/[^a-z0-9_-]+/i', '-', $mediaFieldName));
$mediaFieldLabel = (string) ($mediaFieldLabel ?? 'Image');
$mediaCurrentPath = (string) ($mediaCurrentPath ?? '');
$mediaSelectName = (string) ($mediaSelectName ?? ($mediaFieldName . '_media_id'));
$mediaUploadName = (string) ($mediaUploadName ?? ($mediaFieldName . '_upload'));
$mediaRequired = (bool) ($mediaRequired ?? false);
$mediaHelp = (string) ($mediaHelp ?? 'Choose an existing image or upload a new one. If you leave both untouched, the current image stays in place.');
$mediaOptions = is_array($adminMediaOptions ?? null) ? $adminMediaOptions : [];
$mediaPreviewAlt = (string) ($mediaPreviewAlt ?? ($mediaFieldLabel . ' preview'));
?>
<div class="admin-field admin-media-field">
    <label><?= e($mediaFieldLabel) ?></label>
    <input name="<?= e($mediaFieldName) ?>" type="hidden" value="<?= e($mediaCurrentPath) ?>">

    <?php if ($mediaCurrentPath !== ''): ?>
        <div class="admin-media-field__current">
            <div class="admin-media-field__preview">
                <img src="<?= e(asset($mediaCurrentPath)) ?>" alt="<?= e($mediaPreviewAlt) ?>">
            </div>
            <div class="admin-media-field__meta">
                <strong>Current Asset</strong>
                <code><?= e($mediaCurrentPath) ?></code>
            </div>
        </div>
    <?php endif; ?>

    <div class="admin-media-field__picker">
        <div class="admin-field">
            <label for="<?= e($mediaFieldId) ?>-select">Choose Existing Image</label>
            <select id="<?= e($mediaFieldId) ?>-select" name="<?= e($mediaSelectName) ?>">
                <option value=""><?= e($mediaCurrentPath !== '' ? 'Keep current image' : 'Select from media library') ?></option>
                <?php foreach ($mediaOptions as $mediaOption): ?>
                    <option value="<?= e((string) ($mediaOption['id'] ?? '')) ?>">
                        <?= e((string) ($mediaOption['label'] ?? 'Media')) ?><?= ($mediaOption['file_path'] ?? '') !== '' ? ' - ' . (string) ($mediaOption['file_path'] ?? '') : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-field">
            <label for="<?= e($mediaFieldId) ?>-upload">Upload New Image<?= $mediaRequired && $mediaCurrentPath === '' ? ' *' : '' ?></label>
            <input id="<?= e($mediaFieldId) ?>-upload" name="<?= e($mediaUploadName) ?>" type="file" accept=".jpg,.jpeg,.png,.webp,.svg">
        </div>
    </div>

    <small><?= e($mediaHelp) ?></small>
</div>
