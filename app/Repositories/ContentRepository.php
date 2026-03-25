<?php

declare(strict_types=1);

final class ContentRepository
{
    public function __construct(private readonly array $content)
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
        $gallery = $this->page('gallery');

        $home['about_section']['description'] = array_slice($about['history']['paragraphs'] ?? [], 0, 2);
        $home['events_section']['items'] = $this->homeEventPreview($events);
        $home['gallery_section']['items'] = $this->homeGalleryPreview($gallery);

        return $home;
    }

    public function page(string $slug): array
    {
        return $this->content['pages'][$slug] ?? [];
    }

    public function gallery(string $selectedCategory = 'all'): array
    {
        $page = $this->page('gallery');
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
        $page['ongoing'] = $this->normalizeEvents($page['ongoing'] ?? [], 'ongoing');
        $page['upcoming'] = $this->normalizeEvents($page['upcoming'] ?? [], 'upcoming');
        $page['sponsor_cta']['primary_href'] = route_url('/donations');
        $page['sponsor_cta']['secondary_href'] = route_url('/contact');

        return $page;
    }

    public function eventDetail(string $slug): ?array
    {
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
}
