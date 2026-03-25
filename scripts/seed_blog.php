<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/Env.php';
require __DIR__ . '/../app/Config/Database.php';

$projectRoot = dirname(__DIR__);
$content = require $projectRoot . '/data/site.php';
$blogPage = $content['pages']['blog'] ?? [];

$connection = Database::connection($projectRoot);
$defaultLocale = Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en';
$localeId = locale_id($connection, $defaultLocale);

$posts = collect_posts($blogPage);

$connection->beginTransaction();

try {
    foreach ($posts as $post) {
        $mediaId = upsert_media($connection, $localeId, $post);
        $categoryId = upsert_category($connection, $localeId, $post['category']);
        upsert_post($connection, $localeId, $mediaId, $categoryId, $post);
    }

    $connection->commit();

    echo 'Seeded ' . count($posts) . " blog posts into MySQL.\n";
    echo 'Locale used: ' . $defaultLocale . "\n";
    echo 'Database: ' . (Database::configSummary($projectRoot)['database'] ?? 'unknown') . "\n";
} catch (Throwable $exception) {
    $connection->rollBack();
    fwrite(STDERR, 'Failed to seed blog posts: ' . $exception->getMessage() . PHP_EOL);
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

function collect_posts(array $page): array
{
    $items = [];
    $featured = $page['featured'] ?? [];

    if ($featured !== []) {
        $items[$featured['slug']] = normalize_post($featured, true);
    }

    foreach ($page['posts'] ?? [] as $post) {
        $items[$post['slug']] = normalize_post($post, false);
    }

    foreach (($page['sidebar']['recent_posts'] ?? []) as $post) {
        $items[$post['slug']] = normalize_post($post, false);
    }

    return array_values($items);
}

function normalize_post(array $post, bool $isFeatured): array
{
    $title = (string) ($post['title'] ?? 'Temple Article');
    $excerpt = (string) ($post['description'] ?? $post['excerpt'] ?? '');
    $body = $post['body'] ?? [$excerpt];

    return [
        'slug' => (string) ($post['slug'] ?? slugify($title)),
        'title' => $title,
        'excerpt' => $excerpt,
        'body_long' => implode("\n\n", array_map(static fn (string $paragraph): string => trim($paragraph), $body)),
        'category' => (string) ($post['category'] ?? 'Temple Journal'),
        'image_path' => (string) ($post['image'] ?? 'assets/images/placeholders/blog-post-deepam.svg'),
        'published_at' => published_at_value((string) ($post['date'] ?? '')),
        'read_time_label' => (string) ($post['read_time'] ?? '6 Min Read'),
        'is_featured' => $isFeatured ? 1 : 0,
    ];
}

function published_at_value(string $label): string
{
    $timestamp = strtotime($label);

    if ($timestamp === false) {
        return date('Y-m-d H:i:s');
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function upsert_media(PDO $connection, int $localeId, array $post): int
{
    $statement = $connection->prepare('SELECT id FROM media WHERE file_path = :file_path LIMIT 1');
    $statement->execute(['file_path' => $post['image_path']]);
    $mediaId = $statement->fetchColumn();

    if ($mediaId === false) {
        $insert = $connection->prepare(
            'INSERT INTO media (file_path, original_name, mime_type) VALUES (:file_path, :original_name, :mime_type)'
        );
        $insert->execute([
            'file_path' => $post['image_path'],
            'original_name' => basename($post['image_path']),
            'mime_type' => mime_type_for_path($post['image_path']),
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
        'title' => $post['title'],
        'alt_text' => $post['title'] . ' placeholder artwork',
    ]);

    return $mediaId;
}

function upsert_category(PDO $connection, int $localeId, string $name): int
{
    $slug = slugify($name);

    $statement = $connection->prepare('SELECT id FROM blog_categories WHERE slug = :slug LIMIT 1');
    $statement->execute(['slug' => $slug]);
    $categoryId = $statement->fetchColumn();

    if ($categoryId === false) {
        $insert = $connection->prepare('INSERT INTO blog_categories (slug) VALUES (:slug)');
        $insert->execute(['slug' => $slug]);
        $categoryId = (int) $connection->lastInsertId();
    } else {
        $categoryId = (int) $categoryId;
    }

    $translation = $connection->prepare(
        'INSERT INTO blog_category_translations (category_id, locale_id, name)
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

function upsert_post(PDO $connection, int $localeId, int $mediaId, int $categoryId, array $post): void
{
    $statement = $connection->prepare(
        "INSERT INTO blog_posts (slug, featured_image_id, status, is_featured, published_at)
         VALUES (:slug, :featured_image_id, 'published', :is_featured, :published_at)
         ON DUPLICATE KEY UPDATE
            featured_image_id = VALUES(featured_image_id),
            status = VALUES(status),
            is_featured = VALUES(is_featured),
            published_at = VALUES(published_at)"
    );
    $statement->execute([
        'slug' => $post['slug'],
        'featured_image_id' => $mediaId,
        'is_featured' => $post['is_featured'],
        'published_at' => $post['published_at'],
    ]);

    $lookup = $connection->prepare('SELECT id FROM blog_posts WHERE slug = :slug LIMIT 1');
    $lookup->execute(['slug' => $post['slug']]);
    $postId = $lookup->fetchColumn();

    if ($postId === false) {
        throw new RuntimeException('Unable to resolve seeded blog post ID for slug ' . $post['slug']);
    }

    $translation = $connection->prepare(
        'INSERT INTO blog_post_translations (post_id, locale_id, title, excerpt, body_long, meta_title, meta_description, read_time_label)
         VALUES (:post_id, :locale_id, :title, :excerpt, :body_long, :meta_title, :meta_description, :read_time_label)
         ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            excerpt = VALUES(excerpt),
            body_long = VALUES(body_long),
            meta_title = VALUES(meta_title),
            meta_description = VALUES(meta_description),
            read_time_label = VALUES(read_time_label)'
    );
    $translation->execute([
        'post_id' => (int) $postId,
        'locale_id' => $localeId,
        'title' => $post['title'],
        'excerpt' => $post['excerpt'],
        'body_long' => $post['body_long'],
        'meta_title' => $post['title'],
        'meta_description' => $post['excerpt'],
        'read_time_label' => $post['read_time_label'],
    ]);

    $pivot = $connection->prepare(
        'INSERT IGNORE INTO blog_post_categories (post_id, category_id) VALUES (:post_id, :category_id)'
    );
    $pivot->execute([
        'post_id' => (int) $postId,
        'category_id' => $categoryId,
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

    return $value === '' ? 'item' : $value;
}
