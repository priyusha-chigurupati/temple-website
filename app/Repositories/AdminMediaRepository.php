<?php

declare(strict_types=1);

final class AdminMediaRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function recentOptions(int $limit = 60): array
    {
        $limit = max(1, min(200, $limit));
        $statement = $this->connection->prepare(
            'SELECT
                m.id,
                m.file_path,
                m.original_name,
                COALESCE(mt.title, m.original_name, m.file_path) AS title
             FROM media m
             LEFT JOIN media_translations mt
                ON mt.media_id = m.id
               AND mt.locale_id = :locale_id
             ORDER BY m.id DESC
             LIMIT ' . $limit
        );
        $statement->execute([
            'locale_id' => $this->localeId(),
        ]);

        return array_map(static function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'file_path' => (string) ($row['file_path'] ?? ''),
                'original_name' => (string) ($row['original_name'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'label' => trim((string) ($row['title'] ?? '')) !== ''
                    ? (string) ($row['title'] ?? '')
                    : (string) ($row['original_name'] ?? $row['file_path'] ?? ''),
            ];
        }, $statement->fetchAll() ?: []);
    }

    public function pathForId(int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        $statement = $this->connection->prepare(
            'SELECT file_path FROM media WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $path = $statement->fetchColumn();

        return $path === false ? null : (string) $path;
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
                throw new RuntimeException('No active locale could be resolved for media options.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }
}
