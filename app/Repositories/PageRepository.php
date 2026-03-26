<?php

declare(strict_types=1);

final class PageRepository
{
    private ?int $localeId = null;
    private array $mediaPathCache = [];

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function hasPublishedPage(string $slug): bool
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*)
             FROM pages
             WHERE slug = :slug
               AND status = 'published'"
        );
        $statement->execute(['slug' => $slug]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function page(string $slug): ?array
    {
        $statement = $this->connection->prepare(
            "SELECT
                p.id,
                p.slug,
                p.template_key,
                pt.title,
                pt.meta_title,
                pt.meta_description
            FROM pages p
            INNER JOIN page_translations pt
                ON pt.page_id = p.id
               AND pt.locale_id = :locale_id
            WHERE p.slug = :slug
              AND p.status = 'published'
            LIMIT 1"
        );
        $statement->execute([
            'locale_id' => $this->localeId(),
            'slug' => $slug,
        ]);

        $page = $statement->fetch();

        if (! is_array($page)) {
            return null;
        }

        $sections = $this->sections((int) $page['id']);

        return match ($slug) {
            'about' => $this->aboutPage($page, $sections),
            'contact' => $this->contactPage($page, $sections),
            'home' => $this->homePage($page, $sections),
            default => null,
        };
    }

    private function sections(int $pageId): array
    {
        $statement = $this->connection->prepare(
            "SELECT
                ps.id,
                ps.section_key,
                ps.section_type,
                ps.settings_json,
                ps.image_id,
                pst.eyebrow,
                pst.heading,
                pst.subheading,
                pst.body_long,
                pst.body_json,
                m.file_path AS image_path
            FROM page_sections ps
            INNER JOIN page_section_translations pst
                ON pst.section_id = ps.id
               AND pst.locale_id = :locale_id
            LEFT JOIN media m
                ON m.id = ps.image_id
            WHERE ps.page_id = :page_id
            ORDER BY ps.sort_order ASC, ps.id ASC"
        );
        $statement->execute([
            'locale_id' => $this->localeId(),
            'page_id' => $pageId,
        ]);

        $sections = [];

        foreach ($statement->fetchAll() as $row) {
            $sections[$row['section_key']] = [
                'eyebrow' => $row['eyebrow'] ?? null,
                'heading' => $row['heading'] ?? null,
                'subheading' => $row['subheading'] ?? null,
                'body_long' => $row['body_long'] ?? null,
                'body_json' => $this->jsonValue($row['body_json'] ?? null),
                'settings' => $this->jsonValue($row['settings_json'] ?? null),
                'image_path' => $row['image_path'] ?? null,
            ];
        }

        return $sections;
    }

    private function aboutPage(array $page, array $sections): array
    {
        $hero = $sections['hero'] ?? [];
        $history = $sections['history'] ?? [];
        $mission = $sections['mission'] ?? [];
        $values = $sections['values'] ?? [];
        $facts = $sections['facts'] ?? [];

        $historyBody = is_array($history['body_json'] ?? null) ? $history['body_json'] : [];
        $historySettings = is_array($history['settings'] ?? null) ? $history['settings'] : [];
        $missionBody = is_array($mission['body_json'] ?? null) ? $mission['body_json'] : [];
        $valuesBody = is_array($values['body_json'] ?? null) ? $values['body_json'] : [];
        $valuesSettings = is_array($values['settings'] ?? null) ? $values['settings'] : [];
        $factsBody = is_array($facts['body_json'] ?? null) ? $facts['body_json'] : [];

        return [
            'meta' => [
                'title' => $page['meta_title'] ?? (($page['title'] ?? 'About') . ' | AnkammaThalli Temple'),
                'description' => $page['meta_description'] ?? '',
            ],
            'hero' => [
                'eyebrow' => (string) ($hero['eyebrow'] ?? ''),
                'title' => (string) ($hero['heading'] ?? ''),
                'highlight' => (string) ($hero['subheading'] ?? ''),
                'image' => (string) ($hero['image_path'] ?? ''),
            ],
            'history' => [
                'eyebrow' => (string) ($history['eyebrow'] ?? ''),
                'title' => (string) ($history['heading'] ?? ''),
                'paragraphs' => array_values($historyBody['paragraphs'] ?? []),
                'quote' => (string) ($historyBody['quote'] ?? ''),
                'main_image' => (string) ($history['image_path'] ?? ''),
                'secondary_image' => $this->mediaPathFromSetting($historySettings['secondary_image_id'] ?? null, (string) ($historySettings['secondary_image'] ?? '')),
                'secondary_title' => (string) ($historyBody['secondary_title'] ?? ''),
                'secondary_text' => (string) ($historyBody['secondary_text'] ?? ''),
            ],
            'mission' => [
                'eyebrow' => (string) ($mission['eyebrow'] ?? ''),
                'title' => (string) ($mission['heading'] ?? ''),
                'items' => array_values($missionBody['items'] ?? []),
            ],
            'values' => [
                'intro_title' => (string) ($values['heading'] ?? ''),
                'intro_text' => (string) ($valuesBody['intro_text'] ?? ''),
                'feature_image' => $this->mediaPathFromSetting($valuesSettings['feature_image_id'] ?? null, (string) ($valuesSettings['feature_image'] ?? '')),
                'items' => array_values($valuesBody['items'] ?? []),
            ],
            'facts' => array_values($factsBody['items'] ?? []),
        ];
    }

    private function homePage(array $page, array $sections): array
    {
        $hero = $sections['hero'] ?? [];
        $about = $sections['about_preview'] ?? [];
        $events = $sections['events_preview'] ?? [];
        $gallery = $sections['gallery_preview'] ?? [];
        $donations = $sections['donation_preview'] ?? [];

        $heroSettings = is_array($hero['settings'] ?? null) ? $hero['settings'] : [];
        $aboutBody = is_array($about['body_json'] ?? null) ? $about['body_json'] : [];
        $aboutSettings = is_array($about['settings'] ?? null) ? $about['settings'] : [];
        $eventsSettings = is_array($events['settings'] ?? null) ? $events['settings'] : [];
        $gallerySettings = is_array($gallery['settings'] ?? null) ? $gallery['settings'] : [];
        $donationsBody = is_array($donations['body_json'] ?? null) ? $donations['body_json'] : [];
        $donationsSettings = is_array($donations['settings'] ?? null) ? $donations['settings'] : [];

        return [
            'meta' => [
                'title' => $page['meta_title'] ?? (($page['title'] ?? 'Home') . ' | AnkammaThalli Temple'),
                'description' => $page['meta_description'] ?? '',
            ],
            'hero' => [
                'eyebrow' => (string) ($hero['eyebrow'] ?? ''),
                'title_prefix' => (string) ($heroSettings['title_prefix'] ?? ''),
                'title_highlight' => (string) ($hero['heading'] ?? ''),
                'title_suffix' => (string) ($heroSettings['title_suffix'] ?? ''),
                'description' => (string) ($hero['body_long'] ?? ''),
                'primary_cta' => $heroSettings['primary_cta'] ?? ['label' => 'Support the Temple', 'href' => '/donations'],
                'secondary_cta' => $heroSettings['secondary_cta'] ?? ['label' => 'View Timings', 'href' => '/contact'],
                'background_image' => $this->mediaPathFromSetting($heroSettings['background_image_id'] ?? null, (string) ($heroSettings['background_image'] ?? '')),
                'feature_image' => (string) ($hero['image_path'] ?? ''),
            ],
            'about_section' => [
                'title' => (string) ($about['heading'] ?? ''),
                'description' => array_values($aboutBody['paragraphs'] ?? []),
                'cta' => $aboutSettings['cta'] ?? ['label' => 'Read our full story', 'href' => '/about'],
                'image' => (string) ($about['image_path'] ?? ''),
            ],
            'events_section' => [
                'eyebrow' => (string) ($events['eyebrow'] ?? ''),
                'title' => (string) ($events['heading'] ?? ''),
                'cta' => $eventsSettings['cta'] ?? ['label' => 'View All Calendar', 'href' => '/events'],
                'items' => [],
            ],
            'gallery_section' => [
                'title' => (string) ($gallery['heading'] ?? ''),
                'cta' => $gallerySettings['cta'] ?? ['label' => 'Explore Gallery', 'href' => '/gallery'],
                'items' => [],
            ],
            'donation_section' => [
                'title' => (string) ($donations['heading'] ?? ''),
                'description' => (string) ($donations['body_long'] ?? ''),
                'cards' => array_values($donationsBody['cards'] ?? []),
                'quick_options' => array_values($donationsBody['quick_options'] ?? []),
                'cta' => $donationsSettings['cta'] ?? ['label' => 'Complete Donation', 'href' => '/donations'],
            ],
        ];
    }

    private function contactPage(array $page, array $sections): array
    {
        $hero = $sections['hero'] ?? [];
        $info = $sections['info'] ?? [];
        $form = $sections['form'] ?? [];
        $map = $sections['map'] ?? [];

        $infoBody = is_array($info['body_json'] ?? null) ? $info['body_json'] : [];
        $formBody = is_array($form['body_json'] ?? null) ? $form['body_json'] : [];
        $mapSettings = is_array($map['settings'] ?? null) ? $map['settings'] : [];

        return [
            'meta' => [
                'title' => $page['meta_title'] ?? (($page['title'] ?? 'Contact') . ' | AnkammaThalli Temple'),
                'description' => $page['meta_description'] ?? '',
            ],
            'hero' => [
                'title' => (string) ($hero['heading'] ?? ''),
                'description' => (string) ($hero['body_long'] ?? ''),
                'image' => (string) ($hero['image_path'] ?? ''),
            ],
            'info' => [
                'eyebrow' => (string) ($info['eyebrow'] ?? ''),
                'title' => (string) ($info['heading'] ?? ''),
                'items' => array_values($infoBody['items'] ?? []),
                'social_title' => (string) ($infoBody['social_title'] ?? ''),
                'social_links' => array_values($infoBody['social_links'] ?? []),
            ],
            'form' => [
                'title' => (string) ($form['heading'] ?? ''),
                'description' => (string) ($form['body_long'] ?? ''),
                'fields' => array_values($formBody['fields'] ?? []),
                'button' => (string) ($formBody['button'] ?? 'Submit Message'),
            ],
            'map' => [
                'eyebrow' => (string) ($map['eyebrow'] ?? ''),
                'title' => (string) ($map['heading'] ?? ''),
                'cta' => (string) ($mapSettings['cta'] ?? ''),
                'href' => (string) ($mapSettings['href'] ?? ''),
                'image' => (string) ($map['image_path'] ?? ''),
                'marker' => (string) ($mapSettings['marker'] ?? ''),
            ],
        ];
    }

    private function mediaPathFromSetting(mixed $mediaId, string $fallback): string
    {
        if (is_numeric($mediaId)) {
            $resolved = $this->mediaPath((int) $mediaId);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return $fallback;
    }

    private function mediaPath(int $mediaId): ?string
    {
        if (array_key_exists($mediaId, $this->mediaPathCache)) {
            return $this->mediaPathCache[$mediaId];
        }

        $statement = $this->connection->prepare('SELECT file_path FROM media WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $mediaId]);
        $path = $statement->fetchColumn();

        $this->mediaPathCache[$mediaId] = $path === false ? null : (string) $path;

        return $this->mediaPathCache[$mediaId];
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
                throw new RuntimeException('No active locale could be resolved for pages.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function jsonValue(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
