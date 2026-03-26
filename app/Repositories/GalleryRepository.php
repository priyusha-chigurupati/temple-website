<?php

declare(strict_types=1);

final class GalleryRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function hasPublishedItems(): bool
    {
        $statement = $this->connection->query(
            "SELECT COUNT(*) FROM gallery_items WHERE status = 'published'"
        );

        return (int) $statement->fetchColumn() > 0;
    }

    public function page(string $selectedCategory = 'all'): array
    {
        $filters = $this->filters();
        $validSlugs = array_column($filters, 'slug');

        if ($selectedCategory !== 'all' && ! in_array($selectedCategory, $validSlugs, true)) {
            $selectedCategory = 'all';
        }

        return [
            'selected_category' => $selectedCategory,
            'filters' => array_map(
                static fn (array $filter): array => $filter + ['active' => $filter['slug'] === $selectedCategory],
                $filters
            ),
            'items' => $this->items($selectedCategory),
        ];
    }

    public function homeItems(int $limit = 4): array
    {
        $statement = $this->connection->prepare(
            "SELECT
                gi.id,
                git.title,
                gct.name AS category_name,
                gc.slug AS category_slug,
                m.file_path AS image_path
            FROM gallery_items gi
            INNER JOIN gallery_item_translations git
                ON git.gallery_item_id = gi.id
               AND git.locale_id = :item_locale_id
            INNER JOIN gallery_categories gc
                ON gc.id = gi.category_id
            INNER JOIN gallery_category_translations gct
                ON gct.category_id = gc.id
               AND gct.locale_id = :category_locale_id
            INNER JOIN media m
                ON m.id = gi.image_id
            WHERE gi.status = 'published'
              AND gi.is_featured_home = 1
            ORDER BY gi.sort_order ASC, gi.id ASC
            LIMIT {$limit}"
        );
        $statement->execute([
            'item_locale_id' => $this->localeId(),
            'category_locale_id' => $this->localeId(),
        ]);

        return $this->normalizeItems($statement->fetchAll());
    }

    private function filters(): array
    {
        $statement = $this->connection->prepare(
            "SELECT
                gc.slug,
                gct.name
            FROM gallery_categories gc
            INNER JOIN gallery_category_translations gct
                ON gct.category_id = gc.id
               AND gct.locale_id = :locale_id
            ORDER BY gc.sort_order ASC, gc.id ASC"
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        $filters = [[
            'label' => 'All Collections',
            'slug' => 'all',
        ]];

        foreach ($statement->fetchAll() as $row) {
            $filters[] = [
                'label' => $row['name'] ?? 'Collection',
                'slug' => $row['slug'] ?? 'collection',
            ];
        }

        return $filters;
    }

    private function items(string $selectedCategory): array
    {
        $sql = "SELECT
                gi.id,
                gi.is_featured,
                gi.is_featured_home,
                gi.sort_order,
                git.title,
                git.caption,
                gct.name AS category_name,
                gc.slug AS category_slug,
                m.file_path AS image_path
            FROM gallery_items gi
            INNER JOIN gallery_item_translations git
                ON git.gallery_item_id = gi.id
               AND git.locale_id = :item_locale_id
            INNER JOIN gallery_categories gc
                ON gc.id = gi.category_id
            INNER JOIN gallery_category_translations gct
                ON gct.category_id = gc.id
               AND gct.locale_id = :category_locale_id
            INNER JOIN media m
                ON m.id = gi.image_id
            WHERE gi.status = 'published'";

        $params = [
            'item_locale_id' => $this->localeId(),
            'category_locale_id' => $this->localeId(),
        ];

        if ($selectedCategory !== 'all') {
            $sql .= ' AND gc.slug = :category_slug';
            $params['category_slug'] = $selectedCategory;
        }

        $sql .= ' ORDER BY gi.sort_order ASC, gi.id ASC';

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $this->normalizeItems($statement->fetchAll());
    }

    private function normalizeItems(array $rows): array
    {
        $sizes = ['feature-wide', 'feature-tall', 'square', 'square', 'square', 'wide-banner'];

        return array_map(static function (array $row, int $index) use ($sizes): array {
            return [
                'title' => $row['title'] ?? '',
                'category' => $row['category_name'] ?? '',
                'category_slug' => $row['category_slug'] ?? '',
                'image' => $row['image_path'] ?? 'assets/images/placeholders/gallery-gopuram.svg',
                'size' => $sizes[$index] ?? 'square',
            ];
        }, $rows, array_keys($rows));
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
                throw new RuntimeException('No active locale could be resolved for gallery items.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }
}
