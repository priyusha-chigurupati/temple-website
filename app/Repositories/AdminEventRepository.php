<?php

declare(strict_types=1);

final class AdminEventRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function all(): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                e.id,
                e.slug,
                e.starts_at,
                e.ends_at,
                e.status,
                e.is_featured_home,
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
             ORDER BY
                CASE WHEN e.starts_at IS NULL THEN 1 ELSE 0 END,
                e.starts_at ASC,
                e.id ASC'
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        return array_map(fn (array $row): array => $this->mapRow($row), $statement->fetchAll() ?: []);
    }

    public function listing(array $filters = []): array
    {
        $phase = strtolower(trim((string) ($filters['phase'] ?? 'all')));
        $searchTerm = trim((string) ($filters['q'] ?? ''));
        $requestedPage = max(1, (int) ($filters['page'] ?? 1));
        $allowedPhases = ['all', 'active', 'upcoming', 'completed', 'draft'];

        if (! in_array($phase, $allowedPhases, true)) {
            $phase = 'all';
        }

        $items = $this->all();

        if ($phase !== 'all') {
            $items = array_values(array_filter($items, static fn (array $item): bool => ($item['phase'] ?? '') === $phase));
        }

        if ($searchTerm !== '') {
            $needle = strtolower($searchTerm);
            $items = array_values(array_filter($items, static function (array $item) use ($needle): bool {
                $haystack = strtolower(implode(' ', [
                    $item['title'] ?? '',
                    $item['summary'] ?? '',
                    $item['slug'] ?? '',
                    $item['schedule_label'] ?? '',
                    $item['date_label'] ?? '',
                ]));

                return str_contains($haystack, $needle);
            }));
        }

        $perPage = 5;
        $totalItems = count($items);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = min($requestedPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        return [
            'items' => array_slice($items, $offset, $perPage),
            'phase' => $phase,
            'search_term' => $searchTerm,
            'current_page' => $currentPage,
            'total_items' => $totalItems,
            'pagination' => $this->pagination($currentPage, $totalPages, $phase, $searchTerm),
            'filters' => $this->filters($phase, $searchTerm),
        ];
    }

    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                e.id,
                e.slug,
                e.starts_at,
                e.ends_at,
                e.status,
                e.is_featured_home,
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
             WHERE e.id = :id
             LIMIT 1'
        );
        $statement->execute([
            'locale_id' => $this->localeId(),
            'id' => $id,
        ]);
        $row = $statement->fetch();

        return is_array($row) ? $this->mapRow($row) : null;
    }

    public function save(array $input, ?int $id = null): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $startsAt = trim((string) ($input['starts_at'] ?? ''));
        $endsAt = trim((string) ($input['ends_at'] ?? ''));
        $scheduleLabel = trim((string) ($input['schedule_label'] ?? ''));
        $summary = trim((string) ($input['summary'] ?? ''));
        $bodyLong = trim((string) ($input['body_long'] ?? ''));
        $imagePath = trim((string) ($input['image_path'] ?? ''));
        $sortOrder = trim((string) ($input['sort_order'] ?? '0'));
        $status = trim((string) ($input['status'] ?? 'draft'));
        $isFeaturedHome = isset($input['is_featured_home']) ? 1 : 0;

        if ($slug === '' && $title !== '') {
            $slug = $this->slugify($title);
        }

        $errors = [];

        if ($title === '') {
            $errors[] = 'Please enter the event title.';
        }

        if ($slug === '') {
            $errors[] = 'Please provide a slug.';
        } elseif (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $errors[] = 'Use only lowercase letters, numbers, and hyphens in the slug.';
        }

        if ($startsAt === '' || strtotime($startsAt) === false) {
            $errors[] = 'Please enter a valid start date and time.';
        }

        if ($endsAt !== '' && strtotime($endsAt) === false) {
            $errors[] = 'Please enter a valid end date and time or leave it blank.';
        }

        if ($endsAt !== '' && $startsAt !== '' && strtotime($endsAt) !== false && strtotime($startsAt) !== false && strtotime($endsAt) < strtotime($startsAt)) {
            $errors[] = 'End date must be after the start date.';
        }

        if ($summary === '') {
            $errors[] = 'Please enter the short description.';
        }

        if ($bodyLong === '') {
            $errors[] = 'Please enter the full description.';
        }

        if (! in_array($status, ['draft', 'published'], true)) {
            $errors[] = 'Please choose a valid event status.';
        }

        if ($sortOrder !== '' && ! ctype_digit($sortOrder)) {
            $errors[] = 'Sort order must be a non-negative number.';
        }

        if ($this->slugExists($slug, $id)) {
            $errors[] = 'That slug is already used by another event.';
        }

        $old = [
            'title' => $title,
            'slug' => $slug,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'schedule_label' => $scheduleLabel,
            'summary' => $summary,
            'body_long' => $bodyLong,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
            'status' => $status,
            'is_featured_home' => $isFeaturedHome === 1 ? '1' : '',
        ];

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'old' => $old,
            ];
        }

        $mediaId = $this->upsertMedia($imagePath, $title);

        $this->connection->beginTransaction();

        try {
            if ($id === null) {
                $statement = $this->connection->prepare(
                    'INSERT INTO events (slug, starts_at, ends_at, image_id, status, is_featured_home, sort_order)
                     VALUES (:slug, :starts_at, :ends_at, :image_id, :status, :is_featured_home, :sort_order)'
                );
                $statement->execute([
                    'slug' => $slug,
                    'starts_at' => $this->normalizeDateTime($startsAt),
                    'ends_at' => $endsAt !== '' ? $this->normalizeDateTime($endsAt) : null,
                    'image_id' => $mediaId,
                    'status' => $status,
                    'is_featured_home' => $isFeaturedHome,
                    'sort_order' => (int) ($sortOrder !== '' ? $sortOrder : '0'),
                ]);
                $id = (int) $this->connection->lastInsertId();
            } else {
                $statement = $this->connection->prepare(
                    'UPDATE events
                     SET slug = :slug,
                         starts_at = :starts_at,
                         ends_at = :ends_at,
                         image_id = :image_id,
                         status = :status,
                         is_featured_home = :is_featured_home,
                         sort_order = :sort_order
                     WHERE id = :id'
                );
                $statement->execute([
                    'slug' => $slug,
                    'starts_at' => $this->normalizeDateTime($startsAt),
                    'ends_at' => $endsAt !== '' ? $this->normalizeDateTime($endsAt) : null,
                    'image_id' => $mediaId,
                    'status' => $status,
                    'is_featured_home' => $isFeaturedHome,
                    'sort_order' => (int) ($sortOrder !== '' ? $sortOrder : '0'),
                    'id' => $id,
                ]);
            }

            $translation = $this->connection->prepare(
                'INSERT INTO event_translations (event_id, locale_id, title, summary, body_long, schedule_label)
                 VALUES (:event_id, :locale_id, :title, :summary, :body_long, :schedule_label)
                 ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    summary = VALUES(summary),
                    body_long = VALUES(body_long),
                    schedule_label = VALUES(schedule_label)'
            );
            $translation->execute([
                'event_id' => $id,
                'locale_id' => $this->localeId(),
                'title' => $title,
                'summary' => $summary,
                'body_long' => $bodyLong,
                'schedule_label' => $scheduleLabel,
            ]);

            $this->connection->commit();

            return [
                'ok' => true,
                'id' => $id,
            ];
        } catch (Throwable $exception) {
            $this->connection->rollBack();

            return [
                'ok' => false,
                'errors' => ['The event could not be saved. Please try again.'],
                'old' => $old,
            ];
        }
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM events WHERE id = :id');

        return $statement->execute(['id' => $id]);
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
                throw new RuntimeException('No active locale could be resolved for admin events.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM events WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn() !== false;
    }

    private function upsertMedia(string $filePath, string $title): ?int
    {
        if ($filePath === '') {
            return null;
        }

        $statement = $this->connection->prepare('SELECT id FROM media WHERE file_path = :file_path LIMIT 1');
        $statement->execute(['file_path' => $filePath]);
        $mediaId = $statement->fetchColumn();

        if ($mediaId === false) {
            $insert = $this->connection->prepare(
                'INSERT INTO media (file_path, original_name, mime_type)
                 VALUES (:file_path, :original_name, :mime_type)'
            );
            $insert->execute([
                'file_path' => $filePath,
                'original_name' => basename($filePath),
                'mime_type' => $this->mimeTypeForPath($filePath),
            ]);
            $mediaId = (int) $this->connection->lastInsertId();
        } else {
            $mediaId = (int) $mediaId;
        }

        $translation = $this->connection->prepare(
            'INSERT INTO media_translations (media_id, locale_id, title, alt_text)
             VALUES (:media_id, :locale_id, :title, :alt_text)
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                alt_text = VALUES(alt_text)'
        );
        $translation->execute([
            'media_id' => $mediaId,
            'locale_id' => $this->localeId(),
            'title' => $title,
            'alt_text' => $title,
        ]);

        return $mediaId;
    }

    private function mapRow(array $row): array
    {
        $startsAt = $this->dateTimeValue($row['starts_at'] ?? null);
        $endsAt = $this->dateTimeValue($row['ends_at'] ?? null);
        $phase = $this->phaseFor($startsAt, $endsAt, (string) ($row['status'] ?? 'draft'));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'summary' => (string) ($row['summary'] ?? ''),
            'body_long' => (string) ($row['body_long'] ?? ''),
            'schedule_label' => (string) ($row['schedule_label'] ?? ''),
            'starts_at' => $startsAt?->format('Y-m-d\TH:i') ?? '',
            'ends_at' => $endsAt?->format('Y-m-d\TH:i') ?? '',
            'image_path' => (string) ($row['image_path'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'is_featured_home' => (int) ($row['is_featured_home'] ?? 0),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'phase' => $phase,
            'date_label' => $this->dateLabel($startsAt, $endsAt),
        ];
    }

    private function dateLabel(?DateTimeImmutable $startsAt, ?DateTimeImmutable $endsAt): string
    {
        if ($startsAt === null) {
            return 'Schedule pending';
        }

        if ($endsAt instanceof DateTimeImmutable && $endsAt->format('Y-m-d') !== $startsAt->format('Y-m-d')) {
            return $startsAt->format('M d') . ' - ' . $endsAt->format('M d, Y');
        }

        return $startsAt->format('M d, Y | h:i A');
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

    private function normalizeDateTime(string $value): string
    {
        return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
    }

    private function phaseFor(?DateTimeImmutable $startsAt, ?DateTimeImmutable $endsAt, string $status): string
    {
        if ($status === 'draft') {
            return 'draft';
        }

        if ($startsAt === null) {
            return 'upcoming';
        }

        $now = new DateTimeImmutable('now');
        $today = $now->format('Y-m-d');
        $startsOn = $startsAt->format('Y-m-d');

        if ($endsAt instanceof DateTimeImmutable) {
            if ($endsAt < $now) {
                return 'completed';
            }

            if ($startsAt <= $now && $endsAt >= $now) {
                return 'active';
            }
        } else {
            if ($startsAt <= $now && $startsOn === $today) {
                return 'active';
            }

            if ($startsAt < $now && $startsOn < $today) {
                return 'completed';
            }
        }

        return 'upcoming';
    }

    private function filters(string $selectedPhase, string $searchTerm): array
    {
        $items = [
            'all' => 'All Events',
            'upcoming' => 'Upcoming',
            'active' => 'Active',
            'completed' => 'Completed',
            'draft' => 'Drafts',
        ];

        $filters = [];

        foreach ($items as $slug => $label) {
            $filters[] = [
                'label' => $label,
                'href' => route_url_with_query('/admin/events', [
                    'phase' => $slug === 'all' ? null : $slug,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                ]),
                'active' => $slug === $selectedPhase,
            ];
        }

        return $filters;
    }

    private function pagination(int $currentPage, int $totalPages, string $phase, string $searchTerm): array
    {
        $pages = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            $pages[] = [
                'label' => (string) $page,
                'href' => route_url_with_query('/admin/events', [
                    'phase' => $phase === 'all' ? null : $phase,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => $page === 1 ? null : (string) $page,
                ]),
                'active' => $page === $currentPage,
            ];
        }

        return [
            'previous' => [
                'href' => $currentPage > 1 ? route_url_with_query('/admin/events', [
                    'phase' => $phase === 'all' ? null : $phase,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => $currentPage - 1 === 1 ? null : (string) ($currentPage - 1),
                ]) : null,
            ],
            'pages' => $pages,
            'next' => [
                'href' => $currentPage < $totalPages ? route_url_with_query('/admin/events', [
                    'phase' => $phase === 'all' ? null : $phase,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => (string) ($currentPage + 1),
                ]) : null,
            ],
        ];
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');

        return $value === '' ? 'event' : $value;
    }

    private function mimeTypeForPath(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
