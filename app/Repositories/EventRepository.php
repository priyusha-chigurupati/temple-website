<?php

declare(strict_types=1);

final class EventRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function hasPublishedEvents(): bool
    {
        $statement = $this->connection->query(
            "SELECT COUNT(*) FROM events WHERE status = 'published'"
        );

        return (int) $statement->fetchColumn() > 0;
    }

    public function ongoing(): array
    {
        $statement = $this->connection->prepare(
            "SELECT
                e.id,
                e.slug,
                e.starts_at,
                e.ends_at,
                e.sort_order,
                et.title,
                et.summary,
                et.body_long,
                et.schedule_label,
                m.file_path AS image_path
            FROM events e
            INNER JOIN event_translations et
                ON et.event_id = e.id
               AND et.locale_id = :locale_id
            LEFT JOIN media m
                ON m.id = e.image_id
            WHERE e.status = 'published'
              AND e.starts_at IS NOT NULL
              AND e.starts_at <= NOW()
              AND (e.ends_at IS NULL OR e.ends_at >= NOW())
            ORDER BY e.sort_order ASC, e.starts_at ASC, e.id ASC"
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        return $this->normalizeOngoing($statement->fetchAll());
    }

    public function upcoming(): array
    {
        $statement = $this->connection->prepare(
            "SELECT
                e.id,
                e.slug,
                e.starts_at,
                e.ends_at,
                e.sort_order,
                et.title,
                et.summary,
                et.body_long,
                et.schedule_label,
                m.file_path AS image_path
            FROM events e
            INNER JOIN event_translations et
                ON et.event_id = e.id
               AND et.locale_id = :locale_id
            LEFT JOIN media m
                ON m.id = e.image_id
            WHERE e.status = 'published'
              AND e.starts_at IS NOT NULL
              AND e.starts_at > NOW()
            ORDER BY e.starts_at ASC, e.sort_order ASC, e.id ASC"
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        return $this->normalizeUpcoming($statement->fetchAll());
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->connection->prepare(
            "SELECT
                e.id,
                e.slug,
                e.starts_at,
                e.ends_at,
                e.sort_order,
                et.title,
                et.summary,
                et.body_long,
                et.schedule_label,
                m.file_path AS image_path
            FROM events e
            INNER JOIN event_translations et
                ON et.event_id = e.id
               AND et.locale_id = :locale_id
            LEFT JOIN media m
                ON m.id = e.image_id
            WHERE e.slug = :slug
              AND e.status = 'published'
            LIMIT 1"
        );
        $statement->execute([
            'locale_id' => $this->localeId(),
            'slug' => $slug,
        ]);

        $event = $statement->fetch();

        if (! is_array($event)) {
            return null;
        }

        $status = $this->statusFor($event);
        $schedule = $this->scheduleLabel($event);
        $body = $this->bodyParagraphs($event);
        $statusLabel = $status === 'ongoing' ? 'Ongoing Event' : 'Upcoming Event';

        return [
            'meta' => [
                'title' => ($event['title'] ?? 'Event Details') . ' | AnkammaThalli Temple',
                'description' => $event['summary'] ?? 'Learn more about this temple event.',
            ],
            'eyebrow' => $statusLabel,
            'title' => $event['title'] ?? 'Temple Event',
            'description' => $event['summary'] ?? '',
            'image' => $event['image_path'] ?? 'assets/images/placeholders/events-ongoing-lamps.svg',
            'schedule_label' => $status === 'ongoing' ? 'Currently observed' : 'Scheduled for',
            'schedule' => $schedule,
            'status_badge' => strtoupper($statusLabel),
            'back_href' => route_url('/events'),
            'back_label' => 'Back to Events',
            'body' => $body,
            'quick_facts' => [
                ['label' => 'Status', 'value' => $statusLabel],
                ['label' => 'Calendar', 'value' => $schedule],
                ['label' => 'Temple Page', 'value' => 'AnkammaThalli Events'],
            ],
        ];
    }

    private function localeId(): int
    {
        if ($this->localeId !== null) {
            return $this->localeId;
        }

        $statement = $this->connection->prepare(
            'SELECT id FROM locales WHERE code = :code AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['code' => $this->localeCode]);
        $resolved = $statement->fetchColumn();

        if ($resolved === false) {
            $fallback = $this->connection->query(
                'SELECT id FROM locales WHERE is_default = 1 LIMIT 1'
            )->fetchColumn();

            if ($fallback === false) {
                throw new RuntimeException('No active locale could be resolved for events.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function normalizeOngoing(array $rows): array
    {
        return array_map(function (array $row, int $index): array {
            return [
                'label' => 'Ongoing',
                'date' => $this->scheduleLabel($row),
                'title' => $row['title'] ?? '',
                'description' => $row['summary'] ?? '',
                'cta' => 'Event Details',
                'href' => route_url('/events/' . ($row['slug'] ?? 'event')),
                'image' => $row['image_path'] ?? 'assets/images/placeholders/events-ongoing-lamps.svg',
                'layout' => $index % 2 === 0 ? 'image-left' : 'image-right',
                'status' => 'ongoing',
                'slug' => $row['slug'] ?? 'event',
            ];
        }, $rows, array_keys($rows));
    }

    private function normalizeUpcoming(array $rows): array
    {
        return array_map(function (array $row): array {
            $startsAt = $this->dateTimeValue($row['starts_at'] ?? null);

            return [
                'day' => $startsAt?->format('d') ?? '',
                'month' => $startsAt?->format('F') ?? '',
                'title' => $row['title'] ?? '',
                'description' => $row['summary'] ?? '',
                'cta' => 'Event Details',
                'href' => route_url('/events/' . ($row['slug'] ?? 'event')),
                'image' => $row['image_path'] ?? 'assets/images/placeholders/events-upcoming-vishu.svg',
                'status' => 'upcoming',
                'slug' => $row['slug'] ?? 'event',
            ];
        }, $rows);
    }

    private function statusFor(array $row): string
    {
        $startsAt = $this->dateTimeValue($row['starts_at'] ?? null);
        $endsAt = $this->dateTimeValue($row['ends_at'] ?? null);
        $now = new DateTimeImmutable('now');

        if ($startsAt instanceof DateTimeImmutable && $startsAt <= $now && ($endsAt === null || $endsAt >= $now)) {
            return 'ongoing';
        }

        return 'upcoming';
    }

    private function scheduleLabel(array $row): string
    {
        $label = trim((string) ($row['schedule_label'] ?? ''));

        if ($label !== '') {
            return $label;
        }

        $startsAt = $this->dateTimeValue($row['starts_at'] ?? null);
        $endsAt = $this->dateTimeValue($row['ends_at'] ?? null);

        if ($startsAt === null) {
            return 'Temple schedule to be announced';
        }

        if ($endsAt instanceof DateTimeImmutable && $endsAt->format('Y-m-d') !== $startsAt->format('Y-m-d')) {
            return $startsAt->format('M d') . ' - ' . $endsAt->format('M d');
        }

        return $startsAt->format('M d, Y');
    }

    private function bodyParagraphs(array $row): array
    {
        $body = trim((string) ($row['body_long'] ?? ''));

        if ($body === '') {
            return [
                (string) ($row['summary'] ?? ''),
                'This event detail page is prepared so fuller schedules, registration guidance, and festival-specific instructions can later be managed from the backend without changing the public design.',
            ];
        }

        $parts = preg_split("/\\R{2,}/", $body) ?: [];
        $parts = array_values(array_filter(array_map(static fn (string $part): string => trim($part), $parts)));

        return $parts === [] ? [(string) ($row['summary'] ?? '')] : $parts;
    }

    private function dateTimeValue(mixed $value): ?DateTimeImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }
}
