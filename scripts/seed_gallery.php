<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/Env.php';
require __DIR__ . '/../app/Config/Database.php';

$projectRoot = dirname(__DIR__);
$content = require $projectRoot . '/data/site.php';
$galleryPage = $content['pages']['gallery'] ?? [];

$connection = Database::connection($projectRoot);
$defaultLocale = Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en';
$localeId = locale_id($connection, $defaultLocale);

$items = $galleryPage['items'] ?? [];

$connection->beginTransaction();

try {
    foreach (($galleryPage['filters'] ?? []) as $index => $filter) {
        if (($filter['slug'] ?? '') === 'all') {
            continue;
        }

        upsert_category(
            $connection,
            $localeId,
            (string) ($filter['slug'] ?? 'collection'),
            (string) ($filter['label'] ?? 'Collection'),
            $index
        );
    }

    foreach ($items as $index => $item) {
        $mediaId = upsert_media($connection, $localeId, $item);
        $categoryId = upsert_category(
            $connection,
            $localeId,
            (string) ($item['category_slug'] ?? slugify((string) ($item['category'] ?? 'collection'))),
            (string) ($item['category'] ?? 'Collection'),
            $index + 1
        );
        upsert_gallery_item($connection, $localeId, $mediaId, $categoryId, $item, $index + 1);
    }

    $connection->commit();

    echo 'Seeded ' . count($items) . " gallery items into MySQL.\n";
    echo 'Locale used: ' . $defaultLocale . "\n";
    echo 'Database: ' . (Database::configSummary($projectRoot)['database'] ?? 'unknown') . "\n";
} catch (Throwable $exception) {
    $connection->rollBack();
    fwrite(STDERR, 'Failed to seed gallery items: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

function locale_id(PDO $connection, string $code): int
{
    $statement = $connection->prepare('SELECT id FROM locales WHERE code = :code LIMIT 1');
    $statement->execute(['code' => $code]);
    $localeId = $statement->fetchColumn();

    if ($localeId === false) {
        throw new RuntimeException("Locale '{$code}' was not found in the locales table.");
    }

    return (int) $localeId;
}

function upsert_category(PDO $connection, int $localeId, string $slug, string $name, int $sortOrder): int
{
    $statement = $connection->prepare('SELECT id FROM gallery_categories WHERE slug = :slug LIMIT 1');
    $statement->execute(['slug' => $slug]);
    $categoryId = $statement->fetchColumn();

    if ($categoryId === false) {
        $insert = $connection->prepare('INSERT INTO gallery_categories (slug, sort_order) VALUES (:slug, :sort_order)');
        $insert->execute([
            'slug' => $slug,
            'sort_order' => $sortOrder,
        ]);
        $categoryId = (int) $connection->lastInsertId();
    } else {
        $categoryId = (int) $categoryId;
    }

    $update = $connection->prepare('UPDATE gallery_categories SET sort_order = :sort_order WHERE id = :id');
    $update->execute([
        'sort_order' => $sortOrder,
        'id' => $categoryId,
    ]);

    $translation = $connection->prepare(
        'INSERT INTO gallery_category_translations (category_id, locale_id, name)
         VALUES (:category_id, :locale_id, :name)
         ON DUPLICATE KEY UPDATE name = VALUES(name)'
    );
    $translation->execute([
        'category_id' => $categoryId,
        'locale_id' => $localeId,
        'name' => $name,
    ]);

    return $categoryId;
}

function upsert_media(PDO $connection, int $localeId, array $item): int
{
    $filePath = (string) ($item['image'] ?? 'assets/images/placeholders/gallery-gopuram.svg');
    $statement = $connection->prepare('SELECT id FROM media WHERE file_path = :file_path LIMIT 1');
    $statement->execute(['file_path' => $filePath]);
    $mediaId = $statement->fetchColumn();

    if ($mediaId === false) {
        $insert = $connection->prepare(
            'INSERT INTO media (file_path, original_name, mime_type) VALUES (:file_path, :original_name, :mime_type)'
        );
        $insert->execute([
            'file_path' => $filePath,
            'original_name' => basename($filePath),
            'mime_type' => mime_type_for_path($filePath),
        ]);
        $mediaId = (int) $connection->lastInsertId();
    } else {
        $mediaId = (int) $mediaId;
    }

    $translation = $connection->prepare(
        'INSERT INTO media_translations (media_id, locale_id, title, alt_text)
         VALUES (:media_id, :locale_id, :title, :alt_text)
         ON DUPLICATE KEY UPDATE title = VALUES(title), alt_text = VALUES(alt_text)'
    );
    $translation->execute([
        'media_id' => $mediaId,
        'locale_id' => $localeId,
        'title' => (string) ($item['title'] ?? 'Temple Gallery Image'),
        'alt_text' => (string) ($item['title'] ?? 'Temple Gallery Image') . ' placeholder artwork',
    ]);

    return $mediaId;
}

function upsert_gallery_item(PDO $connection, int $localeId, int $mediaId, int $categoryId, array $item, int $sortOrder): void
{
    $statement = $connection->prepare(
        'SELECT id FROM gallery_items WHERE image_id = :image_id AND category_id = :category_id LIMIT 1'
    );
    $statement->execute([
        'image_id' => $mediaId,
        'category_id' => $categoryId,
    ]);
    $galleryItemId = $statement->fetchColumn();

    $isFeatured = in_array((string) ($item['size'] ?? ''), ['feature-wide', 'feature-tall', 'wide-banner'], true) ? 1 : 0;
    $isFeaturedHome = $sortOrder <= 4 ? 1 : 0;

    if ($galleryItemId === false) {
        $insert = $connection->prepare(
            "INSERT INTO gallery_items (image_id, category_id, status, source_type, is_featured, is_featured_home, sort_order, published_at)
             VALUES (:image_id, :category_id, 'published', 'admin', :is_featured, :is_featured_home, :sort_order, NOW())"
        );
        $insert->execute([
            'image_id' => $mediaId,
            'category_id' => $categoryId,
            'is_featured' => $isFeatured,
            'is_featured_home' => $isFeaturedHome,
            'sort_order' => $sortOrder,
        ]);
        $galleryItemId = (int) $connection->lastInsertId();
    } else {
        $galleryItemId = (int) $galleryItemId;
        $update = $connection->prepare(
            "UPDATE gallery_items
             SET status = 'published',
                 source_type = 'admin',
                 is_featured = :is_featured,
                 is_featured_home = :is_featured_home,
                 sort_order = :sort_order,
                 published_at = COALESCE(published_at, NOW())
             WHERE id = :id"
        );
        $update->execute([
            'is_featured' => $isFeatured,
            'is_featured_home' => $isFeaturedHome,
            'sort_order' => $sortOrder,
            'id' => $galleryItemId,
        ]);
    }

    $translation = $connection->prepare(
        'INSERT INTO gallery_item_translations (gallery_item_id, locale_id, title, caption)
         VALUES (:gallery_item_id, :locale_id, :title, :caption)
         ON DUPLICATE KEY UPDATE title = VALUES(title), caption = VALUES(caption)'
    );
    $translation->execute([
        'gallery_item_id' => $galleryItemId,
        'locale_id' => $localeId,
        'title' => (string) ($item['title'] ?? 'Temple Gallery Image'),
        'caption' => (string) ($item['category'] ?? ''),
    ]);
}

function mime_type_for_path(string $path): string
{
    return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        default => 'application/octet-stream',
    };
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim((string) $value, '-');

    return $value === '' ? 'collection' : $value;
}
