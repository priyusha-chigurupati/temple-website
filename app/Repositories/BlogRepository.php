<?php

declare(strict_types=1);

final class BlogRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function hasPublishedPosts(): bool
    {
        $statement = $this->connection->query(
            "SELECT COUNT(*) FROM blog_posts WHERE status = 'published'"
        );

        return (int) $statement->fetchColumn() > 0;
    }

    public function listing(array $filters = []): array
    {
        $selectedCategory = trim((string) ($filters['category'] ?? 'all'));
        $searchTerm = trim((string) ($filters['q'] ?? ''));
        $requestedPage = max(1, (int) ($filters['page'] ?? 1));

        $posts = $this->allPosts();
        $featured = array_values(array_filter($posts, static fn (array $post): bool => ($post['is_featured'] ?? false) === true))[0] ?? null;
        $library = array_values(array_filter($posts, static fn (array $post): bool => ($post['is_featured'] ?? false) === false));
        $availableCategories = array_map(static fn (array $category): string => $category['slug'], $this->categories($posts, 'all', ''));

        if ($selectedCategory !== 'all' && ! in_array($selectedCategory, $availableCategories, true)) {
            $selectedCategory = 'all';
        }

        if ($selectedCategory !== 'all') {
            $library = array_values(array_filter($library, static fn (array $post): bool => ($post['category_slug'] ?? '') === $selectedCategory));
        }

        if ($searchTerm !== '') {
            $needle = mb_strtolower($searchTerm);
            $library = array_values(array_filter($library, static function (array $post) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    $post['title'] ?? '',
                    $post['excerpt'] ?? '',
                    $post['category'] ?? '',
                    implode(' ', $post['body'] ?? []),
                ]));

                return str_contains($haystack, $needle);
            }));
        }

        usort($library, static fn (array $left, array $right): int => self::timestamp($right) <=> self::timestamp($left));

        $perPage = 4;
        $totalPosts = count($library);
        $totalPages = max(1, (int) ceil($totalPosts / $perPage));
        $currentPage = min($requestedPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $recentLibrary = array_values(array_filter($posts, static fn (array $post): bool => ($post['is_featured'] ?? false) === false));
        usort($recentLibrary, static fn (array $left, array $right): int => self::timestamp($right) <=> self::timestamp($left));

        return [
            'featured' => $featured,
            'posts' => array_slice($library, $offset, $perPage),
            'search_term' => $searchTerm,
            'selected_category' => $selectedCategory,
            'current_page' => $currentPage,
            'pagination' => $this->pagination($currentPage, $totalPages, $selectedCategory, $searchTerm),
            'sidebar_recent_posts' => array_slice($recentLibrary, 0, 3),
            'sidebar_categories' => $this->categories($posts, $selectedCategory, $searchTerm),
        ];
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->allPosts() as $post) {
            if (($post['slug'] ?? '') !== $slug) {
                continue;
            }

            $related = array_values(array_filter(
                $this->allPosts(),
                static fn (array $candidate): bool => ($candidate['slug'] ?? '') !== $slug && ($candidate['is_featured'] ?? false) === false
            ));

            usort($related, static fn (array $left, array $right): int => self::timestamp($right) <=> self::timestamp($left));

            return [
                'eyebrow' => $post['eyebrow'] ?? 'Temple Journal',
                'title' => $post['title'] ?? '',
                'excerpt' => $post['excerpt'] ?? '',
                'category' => $post['category'] ?? '',
                'date' => $post['date'] ?? '',
                'read_time' => $post['read_time'] ?? '',
                'image' => $post['image'] ?? '',
                'body' => $post['body'] ?? [],
                'back_href' => route_url('/blog'),
                'back_label' => 'Back to Blog',
                'related_title' => 'Recent Articles',
                'related_posts' => array_slice($related, 0, 3),
                'meta' => [
                    'title' => ($post['meta_title'] ?? $post['title'] ?? 'Blog Article') . ' | AnkammaThalli Temple',
                    'description' => $post['meta_description'] ?? $post['excerpt'] ?? '',
                ],
            ];
        }

        return null;
    }

    private function allPosts(): array
    {
        $statement = $this->connection->prepare(
            "SELECT
                bp.id,
                bp.slug,
                bp.is_featured,
                bp.published_at,
                bpt.title,
                bpt.excerpt,
                bpt.body_long,
                bpt.meta_title,
                bpt.meta_description,
                bpt.read_time_label,
                bc.slug AS category_slug,
                bct.name AS category_name,
                m.file_path AS image_path
            FROM blog_posts bp
            INNER JOIN blog_post_translations bpt
                ON bpt.post_id = bp.id
               AND bpt.locale_id = :locale_id
            LEFT JOIN blog_post_categories bpc
                ON bpc.post_id = bp.id
            LEFT JOIN blog_categories bc
                ON bc.id = bpc.category_id
            LEFT JOIN blog_category_translations bct
                ON bct.category_id = bc.id
               AND bct.locale_id = :locale_id
            LEFT JOIN media m
                ON m.id = bp.featured_image_id
            WHERE bp.status = 'published'
            ORDER BY bp.published_at DESC, bp.id DESC"
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        $posts = [];

        foreach ($statement->fetchAll() as $row) {
            $postId = (int) $row['id'];

            if (! isset($posts[$postId])) {
                $posts[$postId] = [
                    'slug' => $row['slug'] ?? 'article',
                    'title' => $row['title'] ?? '',
                    'excerpt' => $row['excerpt'] ?? '',
                    'description' => $row['excerpt'] ?? '',
                    'image' => $row['image_path'] ?? 'assets/images/placeholders/blog-post-deepam.svg',
                    'date' => $this->formattedDate($row['published_at'] ?? null),
                    'read_time' => $row['read_time_label'] ?? '6 Min Read',
                    'body' => $this->paragraphs($row['body_long'] ?? '', $row['excerpt'] ?? ''),
                    'href' => route_url('/blog/' . ($row['slug'] ?? 'article')),
                    'cta' => ($row['is_featured'] ?? 0) ? 'Read Full Article' : 'Continue Reading',
                    'is_featured' => (bool) ($row['is_featured'] ?? false),
                    'eyebrow' => ($row['is_featured'] ?? 0) ? 'Featured Journal' : 'Temple Journal',
                    'meta_title' => $row['meta_title'] ?? null,
                    'meta_description' => $row['meta_description'] ?? null,
                    'category' => '',
                    'category_slug' => '',
                ];
            }

            if (($posts[$postId]['category'] ?? '') === '' && ! empty($row['category_name'])) {
                $posts[$postId]['category'] = $row['category_name'];
                $posts[$postId]['category_slug'] = $row['category_slug'] ?? self::slugify((string) $row['category_name']);
            }
        }

        return array_values($posts);
    }

    private function categories(array $posts, string $selectedCategory, string $searchTerm): array
    {
        $counts = [];

        foreach ($posts as $post) {
            if (($post['is_featured'] ?? false) === true) {
                continue;
            }

            $slug = $post['category_slug'] ?? 'temple-journal';
            $label = $post['category'] ?? 'Temple Journal';

            if (! isset($counts[$slug])) {
                $counts[$slug] = [
                    'label' => $label,
                    'count' => 0,
                    'slug' => $slug,
                ];
            }

            $counts[$slug]['count']++;
        }

        uasort($counts, static fn (array $left, array $right): int => strcmp($left['label'], $right['label']));

        return array_map(function (array $category) use ($selectedCategory, $searchTerm): array {
            $category['href'] = route_url_with_query('/blog', [
                'category' => $category['slug'],
                'q' => $searchTerm === '' ? null : $searchTerm,
            ]);
            $category['active'] = $category['slug'] === $selectedCategory;

            return $category;
        }, array_values($counts));
    }

    private function pagination(int $currentPage, int $totalPages, string $category, string $searchTerm): array
    {
        $pages = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            $pages[] = [
                'label' => (string) $page,
                'href' => route_url_with_query('/blog', [
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => $page === 1 ? null : (string) $page,
                ]),
                'active' => $page === $currentPage,
            ];
        }

        return [
            'previous' => [
                'href' => $currentPage > 1 ? route_url_with_query('/blog', [
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => $currentPage - 1 === 1 ? null : (string) ($currentPage - 1),
                ]) : null,
            ],
            'pages' => $pages,
            'next' => [
                'href' => $currentPage < $totalPages ? route_url_with_query('/blog', [
                    'category' => $category === 'all' ? null : $category,
                    'q' => $searchTerm === '' ? null : $searchTerm,
                    'page' => (string) ($currentPage + 1),
                ]) : null,
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
                throw new RuntimeException('No active locale could be resolved for blog posts.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function formattedDate(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '';
        }

        try {
            return (new DateTimeImmutable($value))->format('F d, Y');
        } catch (Exception) {
            return (string) $value;
        }
    }

    private function paragraphs(string $bodyLong, string $excerpt): array
    {
        $bodyLong = trim($bodyLong);

        if ($bodyLong === '') {
            return [$excerpt];
        }

        $parts = preg_split("/\\R{2,}/", $bodyLong) ?: [];
        $parts = array_values(array_filter(array_map(static fn (string $part): string => trim($part), $parts)));

        return $parts === [] ? [$excerpt] : $parts;
    }

    private static function timestamp(array $post): int
    {
        $timestamp = strtotime((string) ($post['date'] ?? ''));

        return $timestamp === false ? 0 : $timestamp;
    }

    private static function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');

        return $value === '' ? 'item' : $value;
    }
}
