<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/Env.php';
require __DIR__ . '/../app/Config/Database.php';

$projectRoot = dirname(__DIR__);
$content = require $projectRoot . '/data/site.php';
$page = $content['pages']['home'] ?? [];

$connection = Database::connection($projectRoot);
$defaultLocale = Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en';
$localeId = locale_id($connection, $defaultLocale);

$connection->beginTransaction();

try {
    $pageId = upsert_page($connection, $localeId, $page);
    $mediaIds = [];

    $mediaIds['hero_feature'] = upsert_media($connection, $localeId, $page['hero']['feature_image'], 'Home Hero Feature Artwork');
    $mediaIds['about_preview'] = upsert_media($connection, $localeId, $page['about_section']['image'], 'Home About Preview Artwork');
    $mediaIds['hero_background'] = upsert_media($connection, $localeId, $page['hero']['background_image'], 'Home Hero Background Artwork');

    upsert_section($connection, $pageId, $localeId, 'hero', 1, $mediaIds['hero_feature'], [
        'eyebrow' => $page['hero']['eyebrow'],
        'heading' => $page['hero']['title_highlight'],
        'body_long' => $page['hero']['description'],
        'settings_json' => [
            'title_prefix' => $page['hero']['title_prefix'],
            'title_suffix' => $page['hero']['title_suffix'],
            'primary_cta' => $page['hero']['primary_cta'],
            'secondary_cta' => $page['hero']['secondary_cta'],
            'background_image_id' => $mediaIds['hero_background'],
        ],
    ]);

    upsert_section($connection, $pageId, $localeId, 'about_preview', 2, $mediaIds['about_preview'], [
        'heading' => $page['about_section']['title'],
        'body_json' => [
            'paragraphs' => $page['about_section']['description'],
        ],
        'settings_json' => [
            'cta' => $page['about_section']['cta'],
        ],
    ]);

    upsert_section($connection, $pageId, $localeId, 'events_preview', 3, null, [
        'eyebrow' => $page['events_section']['eyebrow'],
        'heading' => $page['events_section']['title'],
        'settings_json' => [
            'cta' => $page['events_section']['cta'],
        ],
    ]);

    upsert_section($connection, $pageId, $localeId, 'gallery_preview', 4, null, [
        'heading' => $page['gallery_section']['title'],
        'settings_json' => [
            'cta' => $page['gallery_section']['cta'],
        ],
    ]);

    upsert_section($connection, $pageId, $localeId, 'donation_preview', 5, null, [
        'heading' => $page['donation_section']['title'],
        'body_long' => $page['donation_section']['description'],
        'body_json' => [
            'cards' => $page['donation_section']['cards'],
            'quick_options' => $page['donation_section']['quick_options'],
        ],
        'settings_json' => [
            'cta' => $page['donation_section']['cta'],
        ],
    ]);

    $connection->commit();

    echo "Seeded Home page into MySQL.\n";
    echo 'Locale used: ' . $defaultLocale . "\n";
    echo 'Database: ' . (Database::configSummary($projectRoot)['database'] ?? 'unknown') . "\n";
} catch (Throwable $exception) {
    $connection->rollBack();
    fwrite(STDERR, 'Failed to seed Home page: ' . $exception->getMessage() . PHP_EOL);
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

function upsert_page(PDO $connection, int $localeId, array $page): int
{
    $statement = $connection->prepare(
        "INSERT INTO pages (slug, template_key, status, published_at)
         VALUES ('home', 'home', 'published', NOW())
         ON DUPLICATE KEY UPDATE template_key = VALUES(template_key), status = VALUES(status), published_at = VALUES(published_at)"
    );
    $statement->execute();

    $lookup = $connection->query("SELECT id FROM pages WHERE slug = 'home' LIMIT 1");
    $pageId = $lookup->fetchColumn();

    if ($pageId === false) {
        throw new RuntimeException('Unable to resolve Home page ID.');
    }

    $translation = $connection->prepare(
        'INSERT INTO page_translations (page_id, locale_id, title, meta_title, meta_description)
         VALUES (:page_id, :locale_id, :title, :meta_title, :meta_description)
         ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            meta_title = VALUES(meta_title),
            meta_description = VALUES(meta_description)'
    );
    $translation->execute([
        'page_id' => (int) $pageId,
        'locale_id' => $localeId,
        'title' => 'Home',
        'meta_title' => $page['meta']['title'] ?? 'Home | AnkammaThalli Temple',
        'meta_description' => $page['meta']['description'] ?? '',
    ]);

    return (int) $pageId;
}

function upsert_media(PDO $connection, int $localeId, string $filePath, string $title): int
{
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
        'title' => $title,
        'alt_text' => $title,
    ]);

    return $mediaId;
}

function upsert_section(PDO $connection, int $pageId, int $localeId, string $sectionKey, int $sortOrder, ?int $imageId, array $payload): void
{
    $statement = $connection->prepare(
        'SELECT id FROM page_sections WHERE page_id = :page_id AND section_key = :section_key LIMIT 1'
    );
    $statement->execute([
        'page_id' => $pageId,
        'section_key' => $sectionKey,
    ]);
    $sectionId = $statement->fetchColumn();

    if ($sectionId === false) {
        $insert = $connection->prepare(
            'INSERT INTO page_sections (page_id, section_key, section_type, settings_json, image_id, sort_order)
             VALUES (:page_id, :section_key, :section_type, :settings_json, :image_id, :sort_order)'
        );
        $insert->execute([
            'page_id' => $pageId,
            'section_key' => $sectionKey,
            'section_type' => 'content',
            'settings_json' => json_encode($payload['settings_json'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'image_id' => $imageId,
            'sort_order' => $sortOrder,
        ]);
        $sectionId = (int) $connection->lastInsertId();
    } else {
        $sectionId = (int) $sectionId;
        $update = $connection->prepare(
            'UPDATE page_sections
             SET settings_json = :settings_json,
                 image_id = :image_id,
                 sort_order = :sort_order
             WHERE id = :id'
        );
        $update->execute([
            'settings_json' => json_encode($payload['settings_json'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'image_id' => $imageId,
            'sort_order' => $sortOrder,
            'id' => $sectionId,
        ]);
    }

    $translation = $connection->prepare(
        'INSERT INTO page_section_translations (section_id, locale_id, eyebrow, heading, subheading, body_long, body_json)
         VALUES (:section_id, :locale_id, :eyebrow, :heading, :subheading, :body_long, :body_json)
         ON DUPLICATE KEY UPDATE
            eyebrow = VALUES(eyebrow),
            heading = VALUES(heading),
            subheading = VALUES(subheading),
            body_long = VALUES(body_long),
            body_json = VALUES(body_json)'
    );
    $translation->execute([
        'section_id' => $sectionId,
        'locale_id' => $localeId,
        'eyebrow' => $payload['eyebrow'] ?? null,
        'heading' => $payload['heading'] ?? null,
        'subheading' => $payload['subheading'] ?? null,
        'body_long' => $payload['body_long'] ?? null,
        'body_json' => json_encode($payload['body_json'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
