<?php

declare(strict_types=1);

final class ContentRepository
{
    public function __construct(
        private readonly array $content,
        private readonly ?EventRepository $eventRepository = null,
        private readonly ?BlogRepository $blogRepository = null,
        private readonly ?GalleryRepository $galleryRepository = null,
        private readonly ?PageRepository $pageRepository = null
    )
    {
    }

    public function site(): array
    {
        return $this->content['site'];
    }

    public function navigation(): array
    {
        return $this->content['navigation'];
    }

    public function footer(): array
    {
        return $this->content['footer'];
    }

    public function home(): array
    {
        $home = $this->content['pages']['home'];
        $about = $this->page('about');
        $events = $this->events();
        $gallery = $this->gallery('all');

        $home['about_section']['description'] = array_slice($about['history']['paragraphs'] ?? [], 0, 2);
        $home['events_section']['items'] = $this->homeEventPreview($events);
        $home['gallery_section']['items'] = $this->homeGalleryPreview($gallery);

        return $home;
    }

    public function page(string $slug): array
    {
        if ($this->pageRepository instanceof PageRepository && $this->pageRepository->hasPublishedPage($slug)) {
            $page = $this->pageRepository->page($slug);

            if (is_array($page)) {
                return $page;
            }
        }

        return $this->content['pages'][$slug] ?? [];
    }

    public function gallery(string $selectedCategory = 'all'): array
    {
        $page = $this->page('gallery');

        if ($this->galleryRepository instanceof GalleryRepository && $this->galleryRepository->hasPublishedItems()) {
            $listing = $this->galleryRepository->page($selectedCategory);
            $page['selected_category'] = $listing['selected_category'];
            $page['filters'] = $listing['filters'];
            $page['items'] = $listing['items'];

            return $page;
        }

        $categories = [];

        foreach ($page['filters'] ?? [] as $filter) {
            $categories[] = [
                'label' => $filter['label'],
                'slug' => $filter['slug'],
                'active' => $filter['slug'] === $selectedCategory,
            ];
        }

        $validSlugs = array_column($categories, 'slug');

        if (! in_array($selectedCategory, $validSlugs, true)) {
            $selectedCategory = 'all';
            $categories = array_map(static function (array $filter): array {
                $filter['active'] = $filter['slug'] === 'all';

                return $filter;
            }, $categories);
        }

        $items = $page['items'] ?? [];

        if ($selectedCategory !== 'all') {
            $items = array_values(array_filter($items, static fn (array $item): bool => ($item['category_slug'] ?? '') === $selectedCategory));
        }

        $page['selected_category'] = $selectedCategory;
        $page['filters'] = $categories;
        $page['items'] = $items;

        return $page;
    }

    public function events(): array
    {
        $page = $this->page('events');

        $page['upcoming_section_id'] = 'upcoming-events';
        $page['full_calendar_href'] = route_url('/events') . '#upcoming-events';
        if ($this->eventRepository instanceof EventRepository && $this->eventRepository->hasPublishedEvents()) {
            $page['ongoing'] = $this->eventRepository->ongoing();
            $page['upcoming'] = $this->eventRepository->upcoming();
        } else {
            $page['ongoing'] = $this->normalizeEvents($page['ongoing'] ?? [], 'ongoing');
            $page['upcoming'] = $this->normalizeEvents($page['upcoming'] ?? [], 'upcoming');
        }
        $page['sponsor_cta']['primary_href'] = route_url('/donations');
        $page['sponsor_cta']['secondary_href'] = route_url('/contact');

        return $page;
    }

    public function eventDetail(string $slug): ?array
    {
        if ($this->eventRepository instanceof EventRepository && $this->eventRepository->hasPublishedEvents()) {
            return $this->eventRepository->findBySlug($slug);
        }

        foreach (['ongoing', 'upcoming'] as $group) {
            foreach ($this->events()[$group] ?? [] as $event) {
                if (($event['slug'] ?? '') !== $slug) {
                    continue;
                }

                return $this->buildEventDetailPage($event);
            }
        }

        return null;
    }

    public function blog(array $filters = []): array
    {
        if ($this->blogRepository instanceof BlogRepository && $this->blogRepository->hasPublishedPosts()) {
            $page = $this->page('blog');
            $listing = $this->blogRepository->listing($filters);

            if ($listing['featured'] !== null) {
                $page['featured'] = $listing['featured'];
            }

            $page['posts'] = $listing['posts'];
            $page['search_term'] = $listing['search_term'];
            $page['selected_category'] = $listing['selected_category'];
            $page['current_page'] = $listing['current_page'];
            $page['empty_message'] = 'No blog articles match the current filter yet.';
            $page['chronicles_sort'] = 'Sorted by: Newest';
            $page['pagination'] = $listing['pagination'];
            $page['sidebar']['recent_posts'] = $listing['sidebar_recent_posts'];
            $page['sidebar']['categories'] = $listing['sidebar_categories'];

            return $page;
        }

        $page = $this->page('blog');
        $posts = $this->allBlogPosts($page);
        $featured = array_values(array_filter($posts, static fn (array $post): bool => ($post['is_featured'] ?? false) === true))[0] ?? null;
        $library = array_values(array_filter($posts, static fn (array $post): bool => ($post['is_featured'] ?? false) === false));
        $selectedCategory = (string) ($filters['category'] ?? 'all');
        $searchTerm = trim((string) ($filters['q'] ?? ''));
        $availableCategories = array_map(static fn (array $category): string => $category['slug'], $this->blogCategories($posts, 'all', ''));

        if ($selectedCategory !== 'all' && ! in_array($selectedCategory, $availableCategories, true)) {
            $selectedCategory = 'all';
        }

        if ($selectedCategory !== 'all') {
            $library = array_values(array_filter($library, static fn (array $post): bool => ($post['category_slug'] ?? '') === $selectedCategory));
        }

        if ($searchTerm !== '') {
            $needle = strtolower($searchTerm);
            $library = array_values(array_filter($library, static function (array $post) use ($needle): bool {
                $haystack = strtolower(
                    implode(' ', [
                        $post['title'] ?? '',
                        $post['excerpt'] ?? '',
                        $post['category'] ?? '',
                        implode(' ', $post['body'] ?? []),
                    ])
                );

                return str_contains($haystack, $needle);
            }));
        }

        usort($library, static fn (array $left, array $right): int => self::blogTimestamp($right) <=> self::blogTimestamp($left));

        $perPage = 4;
        $requestedPage = max(1, (int) ($filters['page'] ?? 1));
        $totalPosts = count($library);
        $totalPages = max(1, (int) ceil($totalPosts / $perPage));
        $currentPage = min($requestedPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $page['featured'] = $featured ?? $page['featured'];
        $page['posts'] = array_slice($library, $offset, $perPage);
        $page['search_term'] = $searchTerm;
        $page['selected_category'] = $selectedCategory;
        $page['current_page'] = $currentPage;
        $page['empty_message'] = 'No blog articles match the current filter yet.';
        $page['chronicles_sort'] = 'Sorted by: Newest';
        $page['pagination'] = $this->blogPagination($currentPage, $totalPages, $selectedCategory, $searchTerm);
        $recentLibrary = array_values(array_filter($posts, static fn (array $post): bool => ($post['is_featured'] ?? false) === false));
        usort($recentLibrary, static fn (array $left, array $right): int => self::blogTimestamp($right) <=> self::blogTimestamp($left));
        $page['sidebar']['recent_posts'] = array_slice($recentLibrary, 0, 3);
        $page['sidebar']['categories'] = $this->blogCategories($posts, $selectedCategory, $searchTerm);

        return $page;
    }

    public function blogDetail(string $slug): ?array
    {
        if ($this->blogRepository instanceof BlogRepository && $this->blogRepository->hasPublishedPosts()) {
            return $this->blogRepository->findBySlug($slug);
        }

        foreach ($this->allBlogPosts($this->page('blog')) as $post) {
            if (($post['slug'] ?? '') !== $slug) {
                continue;
            }

            $related = array_values(array_filter(
                $this->allBlogPosts($this->page('blog')),
                static fn (array $candidate): bool => ($candidate['slug'] ?? '') !== $slug && ($candidate['is_featured'] ?? false) === false
            ));

            usort($related, static fn (array $left, array $right): int => self::blogTimestamp($right) <=> self::blogTimestamp($left));

            return [
                'meta' => [
                    'title' => ($post['title'] ?? 'Blog Article') . ' | ' . $this->site()['name'],
                    'description' => $post['excerpt'] ?? '',
                ],
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
            ];
        }

        return null;
    }

    private function homeEventPreview(array $eventsPage): array
    {
        $items = array_map(static function (array $item): array {
            return [
                'date' => $item['date'] ?? '',
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'href' => $item['href'] ?? '/events',
                'image' => $item['image'] ?? '',
            ];
        }, array_slice($eventsPage['ongoing'] ?? [], 0, 3));

        if (count($items) >= 3) {
            return $items;
        }

        $upcoming = $eventsPage['upcoming'] ?? [];

        usort($upcoming, static function (array $left, array $right): int {
            return self::eventSortKey($left) <=> self::eventSortKey($right);
        });

        $remaining = array_slice($upcoming, 0, 3 - count($items));

        foreach ($remaining as $item) {
            $items[] = [
                'date' => trim(($item['day'] ?? '') . ' ' . ($item['month'] ?? '')),
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'href' => $item['href'] ?? '/events',
                'image' => $item['image'] ?? '',
            ];
        }

        return $items;
    }

    private function homeGalleryPreview(array $galleryPage): array
    {
        return array_map(static function (array $item): array {
            return [
                'label' => $item['title'] ?? '',
                'image' => $item['image'] ?? '',
            ];
        }, array_slice($galleryPage['items'] ?? [], 0, 4));
    }

    private static function eventSortKey(array $item): int
    {
        $months = [
            'january' => 1,
            'february' => 2,
            'march' => 3,
            'april' => 4,
            'may' => 5,
            'june' => 6,
            'july' => 7,
            'august' => 8,
            'september' => 9,
            'october' => 10,
            'november' => 11,
            'december' => 12,
        ];

        $month = strtolower((string) ($item['month'] ?? ''));
        $monthValue = $months[$month] ?? 99;
        $dayValue = (int) ($item['day'] ?? 0);

        return ($monthValue * 100) + $dayValue;
    }

    private function normalizeEvents(array $items, string $status): array
    {
        return array_map(function (array $item) use ($status): array {
            $slug = self::slugify((string) ($item['title'] ?? 'event'));

            $item['status'] = $status;
            $item['slug'] = $slug;
            $item['href'] = route_url('/events/' . $slug);
            $item['cta'] = 'Event Details';

            return $item;
        }, $items);
    }

    private function allBlogPosts(array $page): array
    {
        $posts = [];
        $featured = $page['featured'] ?? [];

        if ($featured !== []) {
            $posts[] = $this->normalizeBlogPost(array_merge($featured, ['is_featured' => true, 'eyebrow' => $featured['eyebrow'] ?? 'Featured Journal']));
        }

        foreach ($page['posts'] ?? [] as $post) {
            $posts[] = $this->normalizeBlogPost($post);
        }

        foreach (($page['sidebar']['recent_posts'] ?? []) as $post) {
            $posts[] = $this->normalizeBlogPost($post);
        }

        $unique = [];

        foreach ($posts as $post) {
            $unique[$post['slug']] = $post;
        }

        return array_values($unique);
    }

    private function normalizeBlogPost(array $post): array
    {
        $slug = $post['slug'] ?? self::slugify((string) ($post['title'] ?? 'article'));
        $category = (string) ($post['category'] ?? 'Temple Journal');

        return [
            'slug' => $slug,
            'title' => $post['title'] ?? '',
            'excerpt' => $post['description'] ?? $post['excerpt'] ?? '',
            'description' => $post['description'] ?? $post['excerpt'] ?? '',
            'category' => $category,
            'category_slug' => self::slugify($category),
            'image' => $post['image'] ?? '',
            'date' => $post['date'] ?? '',
            'read_time' => $post['read_time'] ?? '6 Min Read',
            'body' => $post['body'] ?? [$post['description'] ?? $post['excerpt'] ?? ''],
            'eyebrow' => $post['eyebrow'] ?? 'Temple Journal',
            'href' => route_url('/blog/' . $slug),
            'cta' => $post['cta'] ?? 'Continue Reading',
            'is_featured' => (bool) ($post['is_featured'] ?? false),
        ];
    }

    private function blogPagination(int $currentPage, int $totalPages, string $category, string $searchTerm): array
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

    private function blogCategories(array $posts, string $selectedCategory, string $searchTerm): array
    {
        $counts = [];

        foreach ($posts as $post) {
            if (($post['is_featured'] ?? false) === true) {
                continue;
            }

            $slug = $post['category_slug'] ?? 'temple-journal';

            if (! isset($counts[$slug])) {
                $counts[$slug] = [
                    'label' => $post['category'] ?? 'Temple Journal',
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

    private function buildEventDetailPage(array $event): array
    {
        $statusLabel = ($event['status'] ?? 'upcoming') === 'ongoing' ? 'Ongoing Event' : 'Upcoming Event';
        $schedule = $event['date'] ?? trim(($event['day'] ?? '') . ' ' . ($event['month'] ?? ''));

        return [
            'meta' => [
                'title' => ($event['title'] ?? 'Event Details') . ' | ' . $this->site()['name'],
                'description' => $event['description'] ?? 'Learn more about this temple event.',
            ],
            'eyebrow' => $statusLabel,
            'title' => $event['title'] ?? 'Temple Event',
            'description' => $event['description'] ?? '',
            'image' => $event['image'] ?? '',
            'schedule_label' => ($event['status'] ?? 'upcoming') === 'ongoing' ? 'Currently observed' : 'Scheduled for',
            'schedule' => $schedule,
            'status_badge' => strtoupper($statusLabel),
            'back_href' => route_url('/events'),
            'back_label' => 'Back to Events',
            'body' => [
                $event['description'] ?? '',
                'This event detail page is prepared so fuller schedules, registration guidance, and festival-specific instructions can later be managed from the backend without changing the public design.',
            ],
            'quick_facts' => [
                ['label' => 'Status', 'value' => $statusLabel],
                ['label' => 'Calendar', 'value' => $schedule],
                ['label' => 'Temple Page', 'value' => 'AnkammaThalli Events'],
            ],
        ];
    }

    private static function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');

        return $value === '' ? 'event' : $value;
    }

    private static function blogTimestamp(array $post): int
    {
        $timestamp = strtotime((string) ($post['date'] ?? ''));

        return $timestamp === false ? 0 : $timestamp;
    }
}
