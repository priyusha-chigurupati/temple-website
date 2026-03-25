<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/Env.php';
require __DIR__ . '/../app/Config/Database.php';

$projectRoot = dirname(__DIR__);
$content = require $projectRoot . '/data/site.php';
$eventsPage = $content['pages']['events'] ?? [];

$connection = Database::connection($projectRoot);
$defaultLocale = Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en';
$localeId = locale_id($connection, $defaultLocale);

$items = [];
$sortOrder = 1;

foreach (($eventsPage['ongoing'] ?? []) as $index => $item) {
    $timing = ongoing_timing($index);
    $items[] = build_seed_item($item, $timing, $sortOrder++);
}

foreach (($eventsPage['upcoming'] ?? []) as $index => $item) {
    $timing = upcoming_timing($index);
    $items[] = build_seed_item($item, $timing, $sortOrder++);
}

$connection->beginTransaction();

try {
    foreach ($items as $item) {
        $mediaId = upsert_media($connection, $localeId, $item);
        upsert_event($connection, $localeId, $mediaId, $item);
    }

    $connection->commit();

    echo 'Seeded ' . count($items) . " events into MySQL.\n";
    echo "Locale used: {$defaultLocale}\n";
    echo "Database: " . (Database::configSummary($projectRoot)['database'] ?? 'unknown') . "\n";
} catch (Throwable $exception) {
    $connection->rollBack();
    fwrite(STDERR, 'Failed to seed events: ' . $exception->getMessage() . PHP_EOL);
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

function build_seed_item(array $item, array $timing, int $sortOrder): array
{
    $title = (string) ($item['title'] ?? 'Temple Event');
    $summary = (string) ($item['description'] ?? '');

    return [
        'title' => $title,
        'slug' => slugify($title),
        'summary' => $summary,
        'body_long' => trim($summary . "\n\n" . 'This sample event entry was seeded from the current public-site content so the new MySQL-backed version can be tested safely.'),
        'image_path' => (string) ($item['image'] ?? 'assets/images/placeholders/events-ongoing-lamps.svg'),
        'schedule_label' => $timing['schedule_label'],
        'starts_at' => $timing['starts_at'],
        'ends_at' => $timing['ends_at'],
        'sort_order' => $sortOrder,
    ];
}

function ongoing_timing(int $index): array
{
    $today = new DateTimeImmutable('today');

    if ($index === 0) {
        $startsAt = $today->modify('-10 days')->setTime(6, 0);
        $endsAt = $today->modify('+5 days')->setTime(21, 0);

        return [
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $endsAt->format('Y-m-d H:i:s'),
            'schedule_label' => $startsAt->format('M d') . ' - ' . $endsAt->format('M d'),
        ];
    }

    $startsAt = $today->modify('-30 days')->setTime(18, 0);
    $endsAt = $today->modify('+30 days')->setTime(19, 30);

    return [
        'starts_at' => $startsAt->format('Y-m-d H:i:s'),
        'ends_at' => $endsAt->format('Y-m-d H:i:s'),
        'schedule_label' => 'Daily 6:00 PM',
    ];
}

function upcoming_timing(int $index): array
{
    $today = new DateTimeImmutable('today');

    if ($index === 0) {
        $startsAt = $today->modify('first day of next month')->modify('+13 days')->setTime(6, 0);
    } elseif ($index === 1) {
        $startsAt = $today->modify('first day of next month')->modify('+1 month')->setTime(6, 0);
    } else {
        $startsAt = $today->modify('first day of next month')->modify('+1 month')->modify('+21 days')->setTime(18, 30);
    }

    return [
        'starts_at' => $startsAt->format('Y-m-d H:i:s'),
        'ends_at' => null,
        'schedule_label' => $startsAt->format('F d, Y'),
    ];
}

function upsert_media(PDO $connection, int $localeId, array $item): int
{
    $statement = $connection->prepare('SELECT id FROM media WHERE file_path = :file_path LIMIT 1');
    $statement->execute(['file_path' => $item['image_path']]);
    $mediaId = $statement->fetchColumn();

    if ($mediaId === false) {
        $insert = $connection->prepare(
            'INSERT INTO media (file_path, original_name, mime_type) VALUES (:file_path, :original_name, :mime_type)'
        );
        $insert->execute([
            'file_path' => $item['image_path'],
            'original_name' => basename($item['image_path']),
            'mime_type' => mime_type_for_path($item['image_path']),
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
        'title' => $item['title'],
        'alt_text' => $item['title'] . ' placeholder artwork',
    ]);

    return $mediaId;
}

function upsert_event(PDO $connection, int $localeId, int $mediaId, array $item): void
{
    $event = $connection->prepare(
        "INSERT INTO events (slug, starts_at, ends_at, image_id, status, is_featured_home, sort_order)
         VALUES (:slug, :starts_at, :ends_at, :image_id, 'published', 0, :sort_order)
         ON DUPLICATE KEY UPDATE
            starts_at = VALUES(starts_at),
            ends_at = VALUES(ends_at),
            image_id = VALUES(image_id),
            status = VALUES(status),
            sort_order = VALUES(sort_order)"
    );
    $event->execute([
        'slug' => $item['slug'],
        'starts_at' => $item['starts_at'],
        'ends_at' => $item['ends_at'],
        'image_id' => $mediaId,
        'sort_order' => $item['sort_order'],
    ]);

    $lookup = $connection->prepare('SELECT id FROM events WHERE slug = :slug LIMIT 1');
    $lookup->execute(['slug' => $item['slug']]);
    $eventId = $lookup->fetchColumn();

    if ($eventId === false) {
        throw new RuntimeException('Unable to resolve seeded event ID for slug ' . $item['slug']);
    }

    $translation = $connection->prepare(
        'INSERT INTO event_translations (event_id, locale_id, title, summary, body_long, schedule_label)
         VALUES (:event_id, :locale_id, :title, :summary, :body_long, :schedule_label)
         ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            summary = VALUES(summary),
            body_long = VALUES(body_long),
            schedule_label = VALUES(schedule_label)'
    );
    $translation->execute([
        'event_id' => (int) $eventId,
        'locale_id' => $localeId,
        'title' => $item['title'],
        'summary' => $item['summary'],
        'body_long' => $item['body_long'],
        'schedule_label' => $item['schedule_label'],
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

    return $value === '' ? 'event' : $value;
}
