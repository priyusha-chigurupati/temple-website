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
        $events = $this->page('events');
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

    private function homeEventPreview(array $eventsPage): array
    {
        $upcoming = $eventsPage['upcoming'] ?? [];

        usort($upcoming, static function (array $left, array $right): int {
            return self::eventSortKey($left) <=> self::eventSortKey($right);
        });

        $items = array_map(static function (array $item): array {
            return [
                'date' => trim(($item['day'] ?? '') . ' ' . ($item['month'] ?? '')),
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'href' => '/events',
                'image' => $item['image'] ?? '',
            ];
        }, array_slice($upcoming, 0, 3));

        if (count($items) >= 3) {
            return $items;
        }

        $ongoing = array_slice($eventsPage['ongoing'] ?? [], 0, 3 - count($items));

        foreach ($ongoing as $item) {
            $items[] = [
                'date' => $item['date'] ?? '',
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'href' => '/events',
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
}
