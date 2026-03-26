<?php

declare(strict_types=1);

final class AdminPageRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function pages(): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                p.id,
                p.slug,
                p.template_key,
                p.status,
                p.updated_at,
                pt.title,
                pt.meta_title,
                COUNT(ps.id) AS section_count
             FROM pages p
             INNER JOIN page_translations pt
                ON pt.page_id = p.id
               AND pt.locale_id = :locale_id
             LEFT JOIN page_sections ps
                ON ps.page_id = p.id
             GROUP BY p.id, p.slug, p.template_key, p.status, p.updated_at, pt.title, pt.meta_title
             ORDER BY FIELD(p.slug, "home", "about", "contact", "donations"), p.id'
        );
        $statement->execute(['locale_id' => $this->localeId()]);

        return array_map(function (array $row): array {
            $slug = (string) ($row['slug'] ?? '');
            $editable = $slug === 'home';

            return [
                'id' => (int) ($row['id'] ?? 0),
                'slug' => $slug,
                'title' => (string) ($row['title'] ?? ucfirst($slug)),
                'path' => $slug === 'home' ? '/' : '/' . $slug,
                'status' => (string) ($row['status'] ?? 'draft'),
                'section_count' => (int) ($row['section_count'] ?? 0),
                'updated_label' => $this->formattedDate((string) ($row['updated_at'] ?? '')),
                'template_key' => (string) ($row['template_key'] ?? ''),
                'editable' => $editable,
                'public_href' => route_url($slug === 'home' ? '/' : '/' . $slug),
                'edit_href' => $editable ? route_url('/admin/pages/' . $slug) : null,
            ];
        }, $statement->fetchAll() ?: []);
    }

    public function homeEditor(): ?array
    {
        $page = $this->pageWithSections('home');

        if ($page === null) {
            return null;
        }

        $sections = $page['sections'];
        $hero = $sections['hero'] ?? [];
        $heroSettings = $hero['settings'] ?? [];
        $about = $sections['about_preview'] ?? [];
        $aboutBody = $about['body_json'] ?? [];
        $aboutSettings = $about['settings'] ?? [];
        $events = $sections['events_preview'] ?? [];
        $eventsSettings = $events['settings'] ?? [];
        $gallery = $sections['gallery_preview'] ?? [];
        $gallerySettings = $gallery['settings'] ?? [];
        $donation = $sections['donation_preview'] ?? [];
        $donationBody = $donation['body_json'] ?? [];
        $donationSettings = $donation['settings'] ?? [];

        $cards = array_values($donationBody['cards'] ?? []);
        $quickOptions = array_values($donationBody['quick_options'] ?? []);

        return [
            'page' => [
                'title' => (string) ($page['title'] ?? 'Home'),
                'meta_title' => (string) ($page['meta_title'] ?? ''),
                'meta_description' => (string) ($page['meta_description'] ?? ''),
                'status' => (string) ($page['status'] ?? 'published'),
            ],
            'hero' => [
                'eyebrow' => (string) ($hero['eyebrow'] ?? ''),
                'title_prefix' => (string) ($heroSettings['title_prefix'] ?? ''),
                'title_highlight' => (string) ($hero['heading'] ?? ''),
                'title_suffix' => (string) ($heroSettings['title_suffix'] ?? ''),
                'description' => (string) ($hero['body_long'] ?? ''),
                'feature_image' => (string) ($hero['image_path'] ?? ''),
                'background_image' => (string) ($heroSettings['background_image'] ?? ''),
                'primary_cta_label' => (string) (($heroSettings['primary_cta']['label'] ?? '')),
                'primary_cta_href' => (string) (($heroSettings['primary_cta']['href'] ?? '')),
                'secondary_cta_label' => (string) (($heroSettings['secondary_cta']['label'] ?? '')),
                'secondary_cta_href' => (string) (($heroSettings['secondary_cta']['href'] ?? '')),
            ],
            'about_preview' => [
                'title' => (string) ($about['heading'] ?? ''),
                'paragraphs' => implode("\n\n", array_values($aboutBody['paragraphs'] ?? [])),
                'image' => (string) ($about['image_path'] ?? ''),
                'cta_label' => (string) (($aboutSettings['cta']['label'] ?? '')),
                'cta_href' => (string) (($aboutSettings['cta']['href'] ?? '')),
            ],
            'events_preview' => [
                'eyebrow' => (string) ($events['eyebrow'] ?? ''),
                'title' => (string) ($events['heading'] ?? ''),
                'cta_label' => (string) (($eventsSettings['cta']['label'] ?? '')),
                'cta_href' => (string) (($eventsSettings['cta']['href'] ?? '')),
            ],
            'gallery_preview' => [
                'title' => (string) ($gallery['heading'] ?? ''),
                'cta_label' => (string) (($gallerySettings['cta']['label'] ?? '')),
                'cta_href' => (string) (($gallerySettings['cta']['href'] ?? '')),
            ],
            'donation_preview' => [
                'title' => (string) ($donation['heading'] ?? ''),
                'description' => (string) ($donation['body_long'] ?? ''),
                'card_one_title' => (string) (($cards[0]['title'] ?? '')),
                'card_one_description' => (string) (($cards[0]['description'] ?? '')),
                'card_one_button' => (string) (($cards[0]['button'] ?? '')),
                'card_two_title' => (string) (($cards[1]['title'] ?? '')),
                'card_two_description' => (string) (($cards[1]['description'] ?? '')),
                'card_two_button' => (string) (($cards[1]['button'] ?? '')),
                'quick_one_label' => (string) (($quickOptions[0]['label'] ?? '')),
                'quick_one_purpose' => (string) (($quickOptions[0]['purpose'] ?? '')),
                'quick_one_amount' => (string) (($quickOptions[0]['amount'] ?? '')),
                'quick_two_label' => (string) (($quickOptions[1]['label'] ?? '')),
                'quick_two_purpose' => (string) (($quickOptions[1]['purpose'] ?? '')),
                'quick_two_amount' => (string) (($quickOptions[1]['amount'] ?? '')),
                'quick_three_label' => (string) (($quickOptions[2]['label'] ?? '')),
                'quick_three_purpose' => (string) (($quickOptions[2]['purpose'] ?? '')),
                'quick_three_amount' => (string) (($quickOptions[2]['amount'] ?? '')),
                'cta_label' => (string) (($donationSettings['cta']['label'] ?? '')),
                'cta_href' => (string) (($donationSettings['cta']['href'] ?? '')),
            ],
        ];
    }

    public function saveHome(array $input): array
    {
        $old = $this->homeFormFromInput($input);
        $errors = [];

        if (($old['page']['title'] ?? '') === '') {
            $errors[] = 'Please enter the internal page title.';
        }

        if (($old['hero']['title_highlight'] ?? '') === '') {
            $errors[] = 'Please enter the hero highlight title.';
        }

        if (($old['hero']['feature_image'] ?? '') === '') {
            $errors[] = 'Please enter the hero feature image path.';
        }

        if (($old['about_preview']['title'] ?? '') === '') {
            $errors[] = 'Please enter the About preview title.';
        }

        if (($old['about_preview']['image'] ?? '') === '') {
            $errors[] = 'Please enter the About preview image path.';
        }

        if (($old['events_preview']['title'] ?? '') === '') {
            $errors[] = 'Please enter the Events preview title.';
        }

        if (($old['gallery_preview']['title'] ?? '') === '') {
            $errors[] = 'Please enter the Gallery preview title.';
        }

        if (($old['donation_preview']['title'] ?? '') === '') {
            $errors[] = 'Please enter the donation preview title.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'old' => $old,
            ];
        }

        $page = $this->pageWithSections('home');

        if ($page === null) {
            return [
                'ok' => false,
                'errors' => ['The Home page could not be found in the database.'],
                'old' => $old,
            ];
        }

        $this->connection->beginTransaction();

        try {
            $pageUpdate = $this->connection->prepare(
                'UPDATE page_translations
                 SET title = :title,
                     meta_title = :meta_title,
                     meta_description = :meta_description
                 WHERE page_id = :page_id AND locale_id = :locale_id'
            );
            $pageUpdate->execute([
                'title' => $old['page']['title'],
                'meta_title' => $old['page']['meta_title'],
                'meta_description' => $old['page']['meta_description'],
                'page_id' => $page['id'],
                'locale_id' => $this->localeId(),
            ]);

            $heroFeatureMediaId = $this->upsertMedia($old['hero']['feature_image'], 'Home Hero Feature Artwork');
            $heroBackgroundMediaId = $this->upsertMedia($old['hero']['background_image'], 'Home Hero Background Artwork');
            $aboutMediaId = $this->upsertMedia($old['about_preview']['image'], 'Home About Preview Artwork');

            $this->updateSection(
                $page['section_ids']['hero'],
                [
                    'image_id' => $heroFeatureMediaId,
                    'settings_json' => [
                        'title_prefix' => $old['hero']['title_prefix'],
                        'title_suffix' => $old['hero']['title_suffix'],
                        'primary_cta' => [
                            'label' => $old['hero']['primary_cta_label'],
                            'href' => $old['hero']['primary_cta_href'],
                        ],
                        'secondary_cta' => [
                            'label' => $old['hero']['secondary_cta_label'],
                            'href' => $old['hero']['secondary_cta_href'],
                        ],
                        'background_image_id' => $heroBackgroundMediaId,
                        'background_image' => $old['hero']['background_image'],
                    ],
                ],
                [
                    'eyebrow' => $old['hero']['eyebrow'],
                    'heading' => $old['hero']['title_highlight'],
                    'body_long' => $old['hero']['description'],
                ]
            );

            $this->updateSection(
                $page['section_ids']['about_preview'],
                [
                    'image_id' => $aboutMediaId,
                    'settings_json' => [
                        'cta' => [
                            'label' => $old['about_preview']['cta_label'],
                            'href' => $old['about_preview']['cta_href'],
                        ],
                    ],
                ],
                [
                    'heading' => $old['about_preview']['title'],
                    'body_json' => [
                        'paragraphs' => $this->paragraphsFromTextarea($old['about_preview']['paragraphs']),
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['events_preview'],
                [
                    'settings_json' => [
                        'cta' => [
                            'label' => $old['events_preview']['cta_label'],
                            'href' => $old['events_preview']['cta_href'],
                        ],
                    ],
                ],
                [
                    'eyebrow' => $old['events_preview']['eyebrow'],
                    'heading' => $old['events_preview']['title'],
                ]
            );

            $this->updateSection(
                $page['section_ids']['gallery_preview'],
                [
                    'settings_json' => [
                        'cta' => [
                            'label' => $old['gallery_preview']['cta_label'],
                            'href' => $old['gallery_preview']['cta_href'],
                        ],
                    ],
                ],
                [
                    'heading' => $old['gallery_preview']['title'],
                ]
            );

            $this->updateSection(
                $page['section_ids']['donation_preview'],
                [
                    'settings_json' => [
                        'cta' => [
                            'label' => $old['donation_preview']['cta_label'],
                            'href' => $old['donation_preview']['cta_href'],
                        ],
                    ],
                ],
                [
                    'heading' => $old['donation_preview']['title'],
                    'body_long' => $old['donation_preview']['description'],
                    'body_json' => [
                        'cards' => [
                            [
                                'title' => $old['donation_preview']['card_one_title'],
                                'description' => $old['donation_preview']['card_one_description'],
                                'button' => $old['donation_preview']['card_one_button'],
                            ],
                            [
                                'title' => $old['donation_preview']['card_two_title'],
                                'description' => $old['donation_preview']['card_two_description'],
                                'button' => $old['donation_preview']['card_two_button'],
                            ],
                        ],
                        'quick_options' => [
                            [
                                'label' => $old['donation_preview']['quick_one_label'],
                                'purpose' => $old['donation_preview']['quick_one_purpose'],
                                'amount' => $old['donation_preview']['quick_one_amount'],
                                'type' => 'preset',
                            ],
                            [
                                'label' => $old['donation_preview']['quick_two_label'],
                                'purpose' => $old['donation_preview']['quick_two_purpose'],
                                'amount' => $old['donation_preview']['quick_two_amount'],
                                'type' => 'preset',
                            ],
                            [
                                'label' => $old['donation_preview']['quick_three_label'],
                                'purpose' => $old['donation_preview']['quick_three_purpose'],
                                'amount' => $old['donation_preview']['quick_three_amount'],
                                'type' => 'custom',
                            ],
                        ],
                    ],
                ]
            );

            $this->connection->commit();

            return [
                'ok' => true,
            ];
        } catch (Throwable) {
            $this->connection->rollBack();

            return [
                'ok' => false,
                'errors' => ['The Home page could not be saved right now.'],
                'old' => $old,
            ];
        }
    }

    private function pageWithSections(string $slug): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                p.id,
                p.slug,
                p.status,
                pt.title,
                pt.meta_title,
                pt.meta_description,
                ps.id AS section_id,
                ps.section_key,
                ps.settings_json,
                pst.eyebrow,
                pst.heading,
                pst.subheading,
                pst.body_long,
                pst.body_json,
                m.file_path AS image_path
             FROM pages p
             INNER JOIN page_translations pt
                ON pt.page_id = p.id
               AND pt.locale_id = :page_locale_id
             LEFT JOIN page_sections ps
                ON ps.page_id = p.id
             LEFT JOIN page_section_translations pst
                ON pst.section_id = ps.id
               AND pst.locale_id = :section_locale_id
             LEFT JOIN media m
                ON m.id = ps.image_id
             WHERE p.slug = :slug
             ORDER BY ps.sort_order ASC, ps.id ASC'
        );
        $statement->execute([
            'page_locale_id' => $this->localeId(),
            'section_locale_id' => $this->localeId(),
            'slug' => $slug,
        ]);

        $rows = $statement->fetchAll() ?: [];

        if ($rows === []) {
            return null;
        }

        $first = $rows[0];
        $sections = [];
        $sectionIds = [];

        foreach ($rows as $row) {
            $key = (string) ($row['section_key'] ?? '');

            if ($key === '') {
                continue;
            }

            $sectionIds[$key] = (int) ($row['section_id'] ?? 0);
            $sections[$key] = [
                'eyebrow' => (string) ($row['eyebrow'] ?? ''),
                'heading' => (string) ($row['heading'] ?? ''),
                'subheading' => (string) ($row['subheading'] ?? ''),
                'body_long' => (string) ($row['body_long'] ?? ''),
                'body_json' => $this->jsonValue($row['body_json'] ?? null),
                'settings' => $this->jsonValue($row['settings_json'] ?? null),
                'image_path' => (string) ($row['image_path'] ?? ''),
            ];
        }

        return [
            'id' => (int) ($first['id'] ?? 0),
            'slug' => (string) ($first['slug'] ?? ''),
            'title' => (string) ($first['title'] ?? ''),
            'meta_title' => (string) ($first['meta_title'] ?? ''),
            'meta_description' => (string) ($first['meta_description'] ?? ''),
            'status' => (string) ($first['status'] ?? 'draft'),
            'sections' => $sections,
            'section_ids' => $sectionIds,
        ];
    }

    private function updateSection(int $sectionId, array $sectionPayload, array $translationPayload): void
    {
        $sectionUpdate = $this->connection->prepare(
            'UPDATE page_sections
             SET image_id = :image_id,
                 settings_json = :settings_json
             WHERE id = :id'
        );
        $sectionUpdate->execute([
            'image_id' => $sectionPayload['image_id'] ?? null,
            'settings_json' => json_encode($sectionPayload['settings_json'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'id' => $sectionId,
        ]);

        $translationUpdate = $this->connection->prepare(
            'UPDATE page_section_translations
             SET eyebrow = :eyebrow,
                 heading = :heading,
                 subheading = :subheading,
                 body_long = :body_long,
                 body_json = :body_json
             WHERE section_id = :section_id AND locale_id = :locale_id'
        );
        $translationUpdate->execute([
            'eyebrow' => $translationPayload['eyebrow'] ?? null,
            'heading' => $translationPayload['heading'] ?? null,
            'subheading' => $translationPayload['subheading'] ?? null,
            'body_long' => $translationPayload['body_long'] ?? null,
            'body_json' => json_encode($translationPayload['body_json'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'section_id' => $sectionId,
            'locale_id' => $this->localeId(),
        ]);
    }

    private function upsertMedia(string $filePath, string $title): ?int
    {
        if (trim($filePath) === '') {
            return null;
        }

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
            'alt_text' => $title,
        ]);

        return $mediaId;
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
            $fallback = $this->connection->query('SELECT id FROM locales WHERE is_default = 1 LIMIT 1')->fetchColumn();

            if ($fallback === false) {
                throw new RuntimeException('No active locale could be resolved for pages.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function jsonValue(mixed $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function paragraphsFromTextarea(string $value): array
    {
        $parts = preg_split("/\\R{2,}/", trim($value)) ?: [];
        $parts = array_values(array_filter(array_map(static fn (string $part): string => trim($part), $parts)));

        return $parts;
    }

    private function homeFormFromInput(array $input): array
    {
        return [
            'page' => [
                'title' => trim((string) ($input['page_title'] ?? '')),
                'meta_title' => trim((string) ($input['meta_title'] ?? '')),
                'meta_description' => trim((string) ($input['meta_description'] ?? '')),
            ],
            'hero' => [
                'eyebrow' => trim((string) ($input['hero_eyebrow'] ?? '')),
                'title_prefix' => trim((string) ($input['hero_title_prefix'] ?? '')),
                'title_highlight' => trim((string) ($input['hero_title_highlight'] ?? '')),
                'title_suffix' => trim((string) ($input['hero_title_suffix'] ?? '')),
                'description' => trim((string) ($input['hero_description'] ?? '')),
                'feature_image' => trim((string) ($input['hero_feature_image'] ?? '')),
                'background_image' => trim((string) ($input['hero_background_image'] ?? '')),
                'primary_cta_label' => trim((string) ($input['hero_primary_cta_label'] ?? '')),
                'primary_cta_href' => trim((string) ($input['hero_primary_cta_href'] ?? '')),
                'secondary_cta_label' => trim((string) ($input['hero_secondary_cta_label'] ?? '')),
                'secondary_cta_href' => trim((string) ($input['hero_secondary_cta_href'] ?? '')),
            ],
            'about_preview' => [
                'title' => trim((string) ($input['about_title'] ?? '')),
                'paragraphs' => trim((string) ($input['about_paragraphs'] ?? '')),
                'image' => trim((string) ($input['about_image'] ?? '')),
                'cta_label' => trim((string) ($input['about_cta_label'] ?? '')),
                'cta_href' => trim((string) ($input['about_cta_href'] ?? '')),
            ],
            'events_preview' => [
                'eyebrow' => trim((string) ($input['events_eyebrow'] ?? '')),
                'title' => trim((string) ($input['events_title'] ?? '')),
                'cta_label' => trim((string) ($input['events_cta_label'] ?? '')),
                'cta_href' => trim((string) ($input['events_cta_href'] ?? '')),
            ],
            'gallery_preview' => [
                'title' => trim((string) ($input['gallery_title'] ?? '')),
                'cta_label' => trim((string) ($input['gallery_cta_label'] ?? '')),
                'cta_href' => trim((string) ($input['gallery_cta_href'] ?? '')),
            ],
            'donation_preview' => [
                'title' => trim((string) ($input['donation_title'] ?? '')),
                'description' => trim((string) ($input['donation_description'] ?? '')),
                'card_one_title' => trim((string) ($input['donation_card_one_title'] ?? '')),
                'card_one_description' => trim((string) ($input['donation_card_one_description'] ?? '')),
                'card_one_button' => trim((string) ($input['donation_card_one_button'] ?? '')),
                'card_two_title' => trim((string) ($input['donation_card_two_title'] ?? '')),
                'card_two_description' => trim((string) ($input['donation_card_two_description'] ?? '')),
                'card_two_button' => trim((string) ($input['donation_card_two_button'] ?? '')),
                'quick_one_label' => trim((string) ($input['donation_quick_one_label'] ?? '')),
                'quick_one_purpose' => trim((string) ($input['donation_quick_one_purpose'] ?? '')),
                'quick_one_amount' => trim((string) ($input['donation_quick_one_amount'] ?? '')),
                'quick_two_label' => trim((string) ($input['donation_quick_two_label'] ?? '')),
                'quick_two_purpose' => trim((string) ($input['donation_quick_two_purpose'] ?? '')),
                'quick_two_amount' => trim((string) ($input['donation_quick_two_amount'] ?? '')),
                'quick_three_label' => trim((string) ($input['donation_quick_three_label'] ?? '')),
                'quick_three_purpose' => trim((string) ($input['donation_quick_three_purpose'] ?? '')),
                'quick_three_amount' => trim((string) ($input['donation_quick_three_amount'] ?? '')),
                'cta_label' => trim((string) ($input['donation_cta_label'] ?? '')),
                'cta_href' => trim((string) ($input['donation_cta_href'] ?? '')),
            ],
        ];
    }

    private function formattedDate(string $value): string
    {
        if ($value === '') {
            return 'Pending';
        }

        try {
            return (new DateTimeImmutable($value))->format('M d, Y');
        } catch (Exception) {
            return $value;
        }
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
