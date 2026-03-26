<?php

declare(strict_types=1);

final class AdminBlogRepository
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

        $posts = $this->all();
        $categories = $this->categories();
        $validCategorySlugs = array_column($categories, 'slug');

        if ($category !== 'all' && ! in_array($category, $validCategorySlugs, true)) {
            $category = 'all';
        }

        if ($status !== 'all') {
            $posts = array_values(array_filter($posts, static function (array $post) use ($status): bool {
                return match ($status) {
                    'published' => ($post['status'] ?? '') === 'published',
                    'draft' => ($post['status'] ?? '') === 'draft',
                    'featured' => (int) ($post['is_featured'] ?? 0) === 1,
                    default => true,
                };
            }));
        }

        if ($category !== 'all') {
            $posts = array_values(array_filter($posts, static fn (array $post): bool => ($post['category_slug'] ?? '') === $category));
        }

        if ($searchTerm !== '') {
            $needle = mb_strtolower($searchTerm);
            $posts = array_values(array_filter($posts, static function (array $post) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    $post['title'] ?? '',
                    $post['slug'] ?? '',
                    $post['excerpt'] ?? '',
                    $post['body_long'] ?? '',
                    $post['category_name'] ?? '',
                ]));

                return str_contains($haystack, $needle);
            }));
        }

        $perPage = 5;
        $totalItems = count($posts);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = min($requestedPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        return [
            'items' => array_slice($posts, $offset, $perPage),
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
                bp.id,
                bp.slug,
                bp.status,
                bp.is_featured,
                bp.published_at,
                bpt.title,
                bpt.excerpt,
                bpt.body_long,
                bpt.meta_title,
                bpt.meta_description,
                bpt.read_time_label,
                bc.id AS category_id,
                bc.slug AS category_slug,
                bct.name AS category_name,
                m.file_path AS image_path
             FROM blog_posts bp
             INNER JOIN blog_post_translations bpt
                ON bpt.post_id = bp.id
               AND bpt.locale_id = :post_locale_id
             LEFT JOIN blog_post_categories bpc
                ON bpc.post_id = bp.id
             LEFT JOIN blog_categories bc
                ON bc.id = bpc.category_id
             LEFT JOIN blog_category_translations bct
                ON bct.category_id = bc.id
               AND bct.locale_id = :category_locale_id
             LEFT JOIN media m
                ON m.id = bp.featured_image_id
             ORDER BY COALESCE(bp.published_at, bp.created_at) DESC, bp.id DESC'
        );
        $statement->execute([
            'post_locale_id' => $this->localeId(),
            'category_locale_id' => $this->localeId(),
        ]);

        return array_map(fn (array $row): array => $this->mapRow($row), $statement->fetchAll() ?: []);
    }

    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                bp.id,
                bp.slug,
                bp.status,
                bp.is_featured,
                bp.published_at,
                bpt.title,
                bpt.excerpt,
                bpt.body_long,
                bpt.meta_title,
                bpt.meta_description,
                bpt.read_time_label,
                bc.id AS category_id,
                bc.slug AS category_slug,
                bct.name AS category_name,
                m.file_path AS image_path
             FROM blog_posts bp
             INNER JOIN blog_post_translations bpt
                ON bpt.post_id = bp.id
               AND bpt.locale_id = :post_locale_id
             LEFT JOIN blog_post_categories bpc
                ON bpc.post_id = bp.id
             LEFT JOIN blog_categories bc
                ON bc.id = bpc.category_id
             LEFT JOIN blog_category_translations bct
                ON bct.category_id = bc.id
               AND bct.locale_id = :category_locale_id
             LEFT JOIN media m
                ON m.id = bp.featured_image_id
             WHERE bp.id = :id
             LIMIT 1'
        );
        $statement->execute([
            'post_locale_id' => $this->localeId(),
            'category_locale_id' => $this->localeId(),
            'id' => $id,
        ]);
        $row = $statement->fetch();

        return is_array($row) ? $this->mapRow($row) : null;
    }

    public function save(array $input, ?int $id = null): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $excerpt = trim((string) ($input['excerpt'] ?? ''));
        $bodyLong = trim((string) ($input['body_long'] ?? ''));
        $categoryId = trim((string) ($input['category_id'] ?? ''));
        $imagePath = trim((string) ($input['image_path'] ?? ''));
        $readTimeLabel = trim((string) ($input['read_time_label'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'draft'));
        $metaTitle = trim((string) ($input['meta_title'] ?? ''));
        $metaDescription = trim((string) ($input['meta_description'] ?? ''));
        $publishedAt = trim((string) ($input['published_at'] ?? ''));
        $isFeatured = isset($input['is_featured']) ? 1 : 0;

        if ($slug === '') {
            $slug = $this->slugify($title);
        } else {
            $slug = $this->slugify($slug);
        }

        $errors = [];

        if ($title === '') {
            $errors[] = 'Please enter the article title.';
        }

        if ($slug === '') {
            $errors[] = 'Please enter a valid article slug.';
        }

        if ($excerpt === '') {
            $errors[] = 'Please enter the article excerpt.';
        }

        if ($bodyLong === '') {
            $errors[] = 'Please enter the article body.';
        }

        if ($categoryId === '' || ! ctype_digit($categoryId) || ! $this->categoryExists((int) $categoryId)) {
            $errors[] = 'Please choose a valid blog category.';
        }

        if ($imagePath === '') {
            $errors[] = 'Please enter the featured image path.';
        }

        if (! in_array($status, ['draft', 'published'], true)) {
            $errors[] = 'Please choose a valid article status.';
        }

        if ($this->slugExists($slug, $id)) {
            $errors[] = 'That article slug is already in use.';
        }

        if ($readTimeLabel === '') {
            $readTimeLabel = '6 Min Read';
        }

        if ($metaTitle === '') {
            $metaTitle = $title;
        }

        if ($metaDescription === '') {
            $metaDescription = $excerpt;
        }

        $publishedAtValue = null;
        if ($status === 'published') {
            $publishedAtValue = $this->normalizeDateTime($publishedAt) ?? date('Y-m-d H:i:s');
        }

        $old = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'body_long' => $bodyLong,
            'category_id' => $categoryId,
            'image_path' => $imagePath,
            'read_time_label' => $readTimeLabel,
            'status' => $status,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'published_at' => $publishedAt,
            'is_featured' => $isFeatured === 1 ? '1' : '',
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
                    'INSERT INTO blog_posts (slug, featured_image_id, status, is_featured, published_at)
                     VALUES (:slug, :featured_image_id, :status, :is_featured, :published_at)'
                );
                $statement->execute([
                    'slug' => $slug,
                    'featured_image_id' => $mediaId,
                    'status' => $status,
                    'is_featured' => $isFeatured,
                    'published_at' => $publishedAtValue,
                ]);
                $id = (int) $this->connection->lastInsertId();
            } else {
                $statement = $this->connection->prepare(
                    'UPDATE blog_posts
                     SET slug = :slug,
                         featured_image_id = :featured_image_id,
                         status = :status,
                         is_featured = :is_featured,
                         published_at = :published_at
                     WHERE id = :id'
                );
                $statement->execute([
                    'slug' => $slug,
                    'featured_image_id' => $mediaId,
                    'status' => $status,
                    'is_featured' => $isFeatured,
                    'published_at' => $publishedAtValue,
                    'id' => $id,
                ]);
            }

            $translation = $this->connection->prepare(
                'INSERT INTO blog_post_translations
                    (post_id, locale_id, title, excerpt, body_long, meta_title, meta_description, read_time_label)
                 VALUES
                    (:post_id, :locale_id, :title, :excerpt, :body_long, :meta_title, :meta_description, :read_time_label)
                 ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    excerpt = VALUES(excerpt),
                    body_long = VALUES(body_long),
                    meta_title = VALUES(meta_title),
                    meta_description = VALUES(meta_description),
                    read_time_label = VALUES(read_time_label)'
            );
            $translation->execute([
                'post_id' => $id,
                'locale_id' => $this->localeId(),
                'title' => $title,
                'excerpt' => $excerpt,
                'body_long' => $bodyLong,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'read_time_label' => $readTimeLabel,
            ]);

            $this->connection->prepare('DELETE FROM blog_post_categories WHERE post_id = :post_id')
                ->execute(['post_id' => $id]);

            $pivot = $this->connection->prepare(
                'INSERT INTO blog_post_categories (post_id, category_id) VALUES (:post_id, :category_id)'
            );
            $pivot->execute([
                'post_id' => $id,
                'category_id' => (int) $categoryId,
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
                'errors' => ['The article could not be saved right now.'],
                'old' => $old,
            ];
        }
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM blog_posts WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }

    public function categories(): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                bc.id,
                bc.slug,
                bct.name
             FROM blog_categories bc
             INNER JOIN blog_category_translations bct
                ON bct.category_id = bc.id
               AND bct.locale_id = :locale_id
             ORDER BY bct.name ASC, bc.id ASC'
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        return array_map(static fn (array $row): array => [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'name' => (string) ($row['name'] ?? 'Category'),
        ], $statement->fetchAll() ?: []);
    }

    private function statusFilters(string $status, string $category, string $searchTerm): array
    {
        $options = [
            'all' => 'All Articles',
            'published' => 'Published',
            'draft' => 'Drafts',
            'featured' => 'Featured',
        ];

        $filters = [];

        foreach ($options as $value => $label) {
            $filters[] = [
                'label' => $label,
                'active' => $status === $value,
                'href' => route_url_with_query('/admin/blog', [
                    'status' => $value === 'all' ? null : $value,
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                ]),
            ];
        }

        return $filters;
    }

    private function categoryFilters(array $categories, string $status, string $selectedCategory, string $searchTerm): array
    {
        $filters = [[
            'label' => 'All Categories',
            'active' => $selectedCategory === 'all',
            'href' => route_url_with_query('/admin/blog', [
                'status' => $status === 'all' ? null : $status,
                'q' => $searchTerm === '' ? null : $searchTerm,
            ]),
        ]];

        foreach ($categories as $category) {
            $filters[] = [
                'label' => $category['name'],
                'active' => $selectedCategory === $category['slug'],
                'href' => route_url_with_query('/admin/blog', [
                    'status' => $status === 'all' ? null : $status,
                    'category' => $category['slug'],
                    'q' => $searchTerm === '' ? null : $searchTerm,
                ]),
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
                'href' => route_url_with_query('/admin/blog', [
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
                'href' => $currentPage > 1 ? route_url_with_query('/admin/blog', [
                    'status' => $status === 'all' ? null : $status,
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => $currentPage - 1 === 1 ? null : (string) ($currentPage - 1),
                ]) : null,
            ],
            'pages' => $pages,
            'next' => [
                'href' => $currentPage < $totalPages ? route_url_with_query('/admin/blog', [
                    'status' => $status === 'all' ? null : $status,
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => (string) ($currentPage + 1),
                ]) : null,
            ],
        ];
    }

    private function mapRow(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'is_featured' => (int) ($row['is_featured'] ?? 0),
            'published_at' => (string) ($row['published_at'] ?? ''),
            'published_at_form' => $this->publishedAtForm((string) ($row['published_at'] ?? '')),
            'date_label' => $this->formattedDate((string) ($row['published_at'] ?? '')),
            'title' => (string) ($row['title'] ?? ''),
            'excerpt' => (string) ($row['excerpt'] ?? ''),
            'body_long' => (string) ($row['body_long'] ?? ''),
            'meta_title' => (string) ($row['meta_title'] ?? ''),
            'meta_description' => (string) ($row['meta_description'] ?? ''),
            'read_time_label' => (string) ($row['read_time_label'] ?? '6 Min Read'),
            'category_id' => isset($row['category_id']) ? (string) $row['category_id'] : '',
            'category_slug' => (string) ($row['category_slug'] ?? ''),
            'category_name' => (string) ($row['category_name'] ?? ''),
            'image_path' => (string) ($row['image_path'] ?? ''),
        ];
    }

    private function formattedDate(string $value): string
    {
        if ($value === '') {
            return 'Schedule pending';
        }

        try {
            return (new DateTimeImmutable($value))->format('M d, Y | h:i A');
        } catch (Exception) {
            return $value;
        }
    }

    private function publishedAtForm(string $value): string
    {
        if ($value === '') {
            return '';
        }

        try {
            return (new DateTimeImmutable($value))->format('Y-m-d\TH:i');
        } catch (Exception) {
            return '';
        }
    }

    private function normalizeDateTime(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (Exception) {
            return null;
        }
    }

    private function upsertMedia(string $filePath, string $title): int
    {
        $lookup = $this->connection->prepare('SELECT id FROM media WHERE file_path = :file_path LIMIT 1');
        $lookup->execute(['file_path' => $filePath]);
        $mediaId = $lookup->fetchColumn();

        if ($mediaId === false) {
            $insert = $this->connection->prepare(
                'INSERT INTO media (file_path, original_name, mime_type) VALUES (:file_path, :original_name, :mime_type)'
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
             ON DUPLICATE KEY UPDATE title = VALUES(title), alt_text = VALUES(alt_text)'
        );
        $translation->execute([
            'media_id' => $mediaId,
            'locale_id' => $this->localeId(),
            'title' => $title,
            'alt_text' => $title . ' artwork',
        ]);

        return $mediaId;
    }

    private function categoryExists(int $categoryId): bool
    {
        $statement = $this->connection->prepare('SELECT COUNT(*) FROM blog_categories WHERE id = :id');
        $statement->execute(['id' => $categoryId]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function slugExists(string $slug, ?int $exceptId): bool
    {
        $sql = 'SELECT COUNT(*) FROM blog_posts WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
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
                throw new RuntimeException('No active locale could be resolved for blog posts.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');

        return $value === '' ? 'article' : $value;
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
