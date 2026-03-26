<?php

declare(strict_types=1);

final class AdminGalleryRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function listing(array $filters = []): array
    {
        $status = strtolower(trim((string) ($filters['status'] ?? 'all')));
        $category = trim((string) ($filters['category'] ?? 'all'));
        $searchTerm = trim((string) ($filters['q'] ?? ''));
        $requestedPage = max(1, (int) ($filters['page'] ?? 1));
        $allowedStatuses = ['all', 'published', 'draft', 'featured'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $items = $this->all();
        $categories = $this->categories();
        $validCategorySlugs = array_column($categories, 'slug');

        if ($category !== 'all' && ! in_array($category, $validCategorySlugs, true)) {
            $category = 'all';
        }

        if ($status !== 'all') {
            $items = array_values(array_filter($items, static function (array $item) use ($status): bool {
                return match ($status) {
                    'published' => ($item['status'] ?? '') === 'published',
                    'draft' => ($item['status'] ?? '') === 'draft',
                    'featured' => (int) ($item['is_featured_home'] ?? 0) === 1 || (int) ($item['is_featured'] ?? 0) === 1,
                    default => true,
                };
            }));
        }

        if ($category !== 'all') {
            $items = array_values(array_filter($items, static fn (array $item): bool => ($item['category_slug'] ?? '') === $category));
        }

        if ($searchTerm !== '') {
            $needle = strtolower($searchTerm);
            $items = array_values(array_filter($items, static function (array $item) use ($needle): bool {
                $haystack = strtolower(implode(' ', [
                    $item['title'] ?? '',
                    $item['caption'] ?? '',
                    $item['category_name'] ?? '',
                    $item['image_path'] ?? '',
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
            'status' => $status,
            'category' => $category,
            'search_term' => $searchTerm,
            'current_page' => $currentPage,
            'total_items' => $totalItems,
            'filters' => $this->statusFilters($status, $category, $searchTerm),
            'category_filters' => $this->categoryFilters($categories, $status, $category, $searchTerm),
            'pagination' => $this->pagination($currentPage, $totalPages, $status, $category, $searchTerm),
            'categories' => $categories,
        ];
    }

    public function all(): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                gi.id,
                gi.status,
                gi.source_type,
                gi.is_featured,
                gi.is_featured_home,
                gi.sort_order,
                git.title,
                git.caption,
                gc.id AS category_id,
                gc.slug AS category_slug,
                gct.name AS category_name,
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
             ORDER BY gi.sort_order ASC, gi.id ASC'
        );
        $statement->execute([
            'item_locale_id' => $this->localeId(),
            'category_locale_id' => $this->localeId(),
        ]);

        return array_map(fn (array $row): array => $this->mapRow($row), $statement->fetchAll() ?: []);
    }

    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                gi.id,
                gi.status,
                gi.source_type,
                gi.is_featured,
                gi.is_featured_home,
                gi.sort_order,
                git.title,
                git.caption,
                gc.id AS category_id,
                gc.slug AS category_slug,
                gct.name AS category_name,
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
             WHERE gi.id = :id
             LIMIT 1'
        );
        $statement->execute([
            'item_locale_id' => $this->localeId(),
            'category_locale_id' => $this->localeId(),
            'id' => $id,
        ]);
        $row = $statement->fetch();

        return is_array($row) ? $this->mapRow($row) : null;
    }

    public function save(array $input, ?int $id = null): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $caption = trim((string) ($input['caption'] ?? ''));
        $categoryId = trim((string) ($input['category_id'] ?? ''));
        $imagePath = trim((string) ($input['image_path'] ?? ''));
        $sortOrder = trim((string) ($input['sort_order'] ?? '0'));
        $status = trim((string) ($input['status'] ?? 'draft'));
        $isFeatured = isset($input['is_featured']) ? 1 : 0;
        $isFeaturedHome = isset($input['is_featured_home']) ? 1 : 0;

        $errors = [];

        if ($title === '') {
            $errors[] = 'Please enter the gallery title.';
        }

        if ($categoryId === '' || ! ctype_digit($categoryId) || ! $this->categoryExists((int) $categoryId)) {
            $errors[] = 'Please choose a valid gallery category.';
        }

        if ($imagePath === '') {
            $errors[] = 'Please enter the image path.';
        }

        if (! in_array($status, ['draft', 'published'], true)) {
            $errors[] = 'Please choose a valid gallery status.';
        }

        if ($sortOrder !== '' && ! ctype_digit($sortOrder)) {
            $errors[] = 'Sort order must be a non-negative number.';
        }

        $old = [
            'title' => $title,
            'caption' => $caption,
            'category_id' => $categoryId,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
            'status' => $status,
            'is_featured' => $isFeatured === 1 ? '1' : '',
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
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $this->connection->beginTransaction();

        try {
            if ($id === null) {
                $statement = $this->connection->prepare(
                    'INSERT INTO gallery_items (image_id, category_id, status, source_type, is_featured, is_featured_home, sort_order, published_at)
                     VALUES (:image_id, :category_id, :status, :source_type, :is_featured, :is_featured_home, :sort_order, :published_at)'
                );
                $statement->execute([
                    'image_id' => $mediaId,
                    'category_id' => (int) $categoryId,
                    'status' => $status,
                    'source_type' => 'admin',
                    'is_featured' => $isFeatured,
                    'is_featured_home' => $isFeaturedHome,
                    'sort_order' => (int) ($sortOrder !== '' ? $sortOrder : '0'),
                    'published_at' => $publishedAt,
                ]);
                $id = (int) $this->connection->lastInsertId();
            } else {
                $statement = $this->connection->prepare(
                    'UPDATE gallery_items
                     SET image_id = :image_id,
                         category_id = :category_id,
                         status = :status,
                         is_featured = :is_featured,
                         is_featured_home = :is_featured_home,
                         sort_order = :sort_order,
                         published_at = :published_at
                     WHERE id = :id'
                );
                $statement->execute([
                    'image_id' => $mediaId,
                    'category_id' => (int) $categoryId,
                    'status' => $status,
                    'is_featured' => $isFeatured,
                    'is_featured_home' => $isFeaturedHome,
                    'sort_order' => (int) ($sortOrder !== '' ? $sortOrder : '0'),
                    'published_at' => $publishedAt,
                    'id' => $id,
                ]);
            }

            $translation = $this->connection->prepare(
                'INSERT INTO gallery_item_translations (gallery_item_id, locale_id, title, caption)
                 VALUES (:gallery_item_id, :locale_id, :title, :caption)
                 ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    caption = VALUES(caption)'
            );
            $translation->execute([
                'gallery_item_id' => $id,
                'locale_id' => $this->localeId(),
                'title' => $title,
                'caption' => $caption,
            ]);

            $this->connection->commit();

            return [
                'ok' => true,
                'id' => $id,
            ];
        } catch (Throwable) {
            $this->connection->rollBack();

            return [
                'ok' => false,
                'errors' => ['The gallery item could not be saved. Please try again.'],
                'old' => $old,
            ];
        }
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM gallery_items WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }

    public function categories(): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                gc.id,
                gc.slug,
                gct.name
             FROM gallery_categories gc
             INNER JOIN gallery_category_translations gct
                ON gct.category_id = gc.id
               AND gct.locale_id = :locale_id
             ORDER BY gc.sort_order ASC, gc.id ASC'
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        return $statement->fetchAll() ?: [];
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
                throw new RuntimeException('No active locale could be resolved for admin gallery.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function categoryExists(int $id): bool
    {
        $statement = $this->connection->prepare('SELECT id FROM gallery_categories WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    private function upsertMedia(string $filePath, string $title): int
    {
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
        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'caption' => (string) ($row['caption'] ?? ''),
            'category_id' => (int) ($row['category_id'] ?? 0),
            'category_slug' => (string) ($row['category_slug'] ?? ''),
            'category_name' => (string) ($row['category_name'] ?? ''),
            'image_path' => (string) ($row['image_path'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'source_type' => (string) ($row['source_type'] ?? 'admin'),
            'is_featured' => (int) ($row['is_featured'] ?? 0),
            'is_featured_home' => (int) ($row['is_featured_home'] ?? 0),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ];
    }

    private function statusFilters(string $selectedStatus, string $category, string $searchTerm): array
    {
        $items = [
            'all' => 'All Media',
            'published' => 'Published',
            'draft' => 'Drafts',
            'featured' => 'Featured',
        ];

        $filters = [];

        foreach ($items as $slug => $label) {
            $filters[] = [
                'label' => $label,
                'href' => route_url_with_query('/admin/media', [
                    'status' => $slug === 'all' ? null : $slug,
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                ]),
                'active' => $slug === $selectedStatus,
            ];
        }

        return $filters;
    }

    private function categoryFilters(array $categories, string $status, string $selectedCategory, string $searchTerm): array
    {
        $filters = [[
            'label' => 'All Categories',
            'href' => route_url_with_query('/admin/media', [
                'status' => $status === 'all' ? null : $status,
                'q' => $searchTerm === '' ? null : $searchTerm,
            ]),
            'active' => $selectedCategory === 'all',
        ]];

        foreach ($categories as $category) {
            $filters[] = [
                'label' => (string) ($category['name'] ?? 'Category'),
                'href' => route_url_with_query('/admin/media', [
                    'status' => $status === 'all' ? null : $status,
                    'category' => (string) ($category['slug'] ?? ''),
                    'q' => $searchTerm === '' ? null : $searchTerm,
                ]),
                'active' => (string) ($category['slug'] ?? '') === $selectedCategory,
            ];
        }

        return $filters;
    }

    private function pagination(int $currentPage, int $totalPages, string $status, string $category, string $searchTerm): array
    {
        $pages = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            $pages[] = [
                'label' => (string) $page,
                'href' => route_url_with_query('/admin/media', [
                    'status' => $status === 'all' ? null : $status,
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => $page === 1 ? null : (string) $page,
                ]),
                'active' => $page === $currentPage,
            ];
        }

        return [
            'previous' => [
                'href' => $currentPage > 1 ? route_url_with_query('/admin/media', [
                    'status' => $status === 'all' ? null : $status,
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => $currentPage - 1 === 1 ? null : (string) ($currentPage - 1),
                ]) : null,
            ],
            'pages' => $pages,
            'next' => [
                'href' => $currentPage < $totalPages ? route_url_with_query('/admin/media', [
                    'status' => $status === 'all' ? null : $status,
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => (string) ($currentPage + 1),
                ]) : null,
            ],
        ];
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
