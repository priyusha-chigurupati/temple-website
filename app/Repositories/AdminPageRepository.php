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
            $editable = in_array($slug, ['home', 'about', 'contact', 'donations'], true);

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
            'page' => $this->pageMetaFromPage($page),
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
            return ['ok' => false, 'errors' => $errors, 'old' => $old];
        }

        $page = $this->pageWithSections('home');

        if ($page === null) {
            return ['ok' => false, 'errors' => ['The Home page could not be found in the database.'], 'old' => $old];
        }

        $this->connection->beginTransaction();

        try {
            $this->updatePageMeta($page['id'], $old['page']);

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

            return ['ok' => true];
        } catch (Throwable) {
            $this->connection->rollBack();

            return ['ok' => false, 'errors' => ['The Home page could not be saved right now.'], 'old' => $old];
        }
    }

    public function aboutEditor(): ?array
    {
        $page = $this->pageWithSections('about');

        if ($page === null) {
            return null;
        }

        $sections = $page['sections'];
        $hero = $sections['hero'] ?? [];
        $history = $sections['history'] ?? [];
        $historyBody = $history['body_json'] ?? [];
        $historySettings = $history['settings'] ?? [];
        $mission = $sections['mission'] ?? [];
        $missionItems = array_values(($mission['body_json']['items'] ?? []));
        $values = $sections['values'] ?? [];
        $valuesBody = $values['body_json'] ?? [];
        $valuesItems = array_values($valuesBody['items'] ?? []);
        $valuesSettings = $values['settings'] ?? [];
        $facts = array_values(($sections['facts']['body_json']['items'] ?? []));

        return [
            'page' => $this->pageMetaFromPage($page),
            'hero' => [
                'eyebrow' => (string) ($hero['eyebrow'] ?? ''),
                'title' => (string) ($hero['heading'] ?? ''),
                'highlight' => (string) ($hero['subheading'] ?? ''),
                'image' => (string) ($hero['image_path'] ?? ''),
            ],
            'history' => [
                'eyebrow' => (string) ($history['eyebrow'] ?? ''),
                'title' => (string) ($history['heading'] ?? ''),
                'paragraphs' => implode("\n\n", array_values($historyBody['paragraphs'] ?? [])),
                'quote' => (string) ($historyBody['quote'] ?? ''),
                'main_image' => (string) ($history['image_path'] ?? ''),
                'secondary_image' => (string) ($historySettings['secondary_image'] ?? ''),
                'secondary_title' => (string) ($historyBody['secondary_title'] ?? ''),
                'secondary_text' => (string) ($historyBody['secondary_text'] ?? ''),
            ],
            'mission' => [
                'eyebrow' => (string) ($mission['eyebrow'] ?? ''),
                'title' => (string) ($mission['heading'] ?? ''),
                'item_one_symbol' => (string) (($missionItems[0]['symbol'] ?? '')),
                'item_one_title' => (string) (($missionItems[0]['title'] ?? '')),
                'item_one_description' => (string) (($missionItems[0]['description'] ?? '')),
                'item_two_symbol' => (string) (($missionItems[1]['symbol'] ?? '')),
                'item_two_title' => (string) (($missionItems[1]['title'] ?? '')),
                'item_two_description' => (string) (($missionItems[1]['description'] ?? '')),
                'item_three_symbol' => (string) (($missionItems[2]['symbol'] ?? '')),
                'item_three_title' => (string) (($missionItems[2]['title'] ?? '')),
                'item_three_description' => (string) (($missionItems[2]['description'] ?? '')),
            ],
            'values' => [
                'intro_title' => (string) ($values['heading'] ?? ''),
                'intro_text' => (string) ($valuesBody['intro_text'] ?? ''),
                'feature_image' => (string) ($valuesSettings['feature_image'] ?? ''),
                'item_one_number' => (string) (($valuesItems[0]['number'] ?? '')),
                'item_one_title' => (string) (($valuesItems[0]['title'] ?? '')),
                'item_one_description' => (string) (($valuesItems[0]['description'] ?? '')),
                'item_two_number' => (string) (($valuesItems[1]['number'] ?? '')),
                'item_two_title' => (string) (($valuesItems[1]['title'] ?? '')),
                'item_two_description' => (string) (($valuesItems[1]['description'] ?? '')),
                'item_three_number' => (string) (($valuesItems[2]['number'] ?? '')),
                'item_three_title' => (string) (($valuesItems[2]['title'] ?? '')),
                'item_three_description' => (string) (($valuesItems[2]['description'] ?? '')),
                'item_four_number' => (string) (($valuesItems[3]['number'] ?? '')),
                'item_four_title' => (string) (($valuesItems[3]['title'] ?? '')),
                'item_four_description' => (string) (($valuesItems[3]['description'] ?? '')),
            ],
            'facts' => [
                'fact_one_value' => (string) (($facts[0]['value'] ?? '')),
                'fact_one_label' => (string) (($facts[0]['label'] ?? '')),
                'fact_two_value' => (string) (($facts[1]['value'] ?? '')),
                'fact_two_label' => (string) (($facts[1]['label'] ?? '')),
                'fact_three_value' => (string) (($facts[2]['value'] ?? '')),
                'fact_three_label' => (string) (($facts[2]['label'] ?? '')),
                'fact_four_value' => (string) (($facts[3]['value'] ?? '')),
                'fact_four_label' => (string) (($facts[3]['label'] ?? '')),
            ],
        ];
    }

    public function saveAbout(array $input): array
    {
        $old = $this->aboutFormFromInput($input);
        $errors = [];

        if (($old['page']['title'] ?? '') === '') {
            $errors[] = 'Please enter the internal page title.';
        }
        if (($old['hero']['title'] ?? '') === '') {
            $errors[] = 'Please enter the About hero title.';
        }
        if (($old['hero']['image'] ?? '') === '') {
            $errors[] = 'Please enter the About hero image path.';
        }
        if (($old['history']['title'] ?? '') === '') {
            $errors[] = 'Please enter the History section title.';
        }
        if (($old['history']['main_image'] ?? '') === '') {
            $errors[] = 'Please enter the History main image path.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'old' => $old];
        }

        $page = $this->pageWithSections('about');

        if ($page === null) {
            return ['ok' => false, 'errors' => ['The About page could not be found in the database.'], 'old' => $old];
        }

        $this->connection->beginTransaction();

        try {
            $this->updatePageMeta($page['id'], $old['page']);

            $heroMediaId = $this->upsertMedia($old['hero']['image'], 'About Hero Artwork');
            $historyMainMediaId = $this->upsertMedia($old['history']['main_image'], 'About History Main Artwork');
            $historySecondaryMediaId = $this->upsertMedia($old['history']['secondary_image'], 'About History Secondary Artwork');
            $valuesFeatureMediaId = $this->upsertMedia($old['values']['feature_image'], 'About Values Feature Artwork');

            $this->updateSection(
                $page['section_ids']['hero'],
                ['image_id' => $heroMediaId, 'settings_json' => null],
                [
                    'eyebrow' => $old['hero']['eyebrow'],
                    'heading' => $old['hero']['title'],
                    'subheading' => $old['hero']['highlight'],
                ]
            );

            $this->updateSection(
                $page['section_ids']['history'],
                [
                    'image_id' => $historyMainMediaId,
                    'settings_json' => [
                        'secondary_image_id' => $historySecondaryMediaId,
                        'secondary_image' => $old['history']['secondary_image'],
                    ],
                ],
                [
                    'eyebrow' => $old['history']['eyebrow'],
                    'heading' => $old['history']['title'],
                    'body_json' => [
                        'paragraphs' => $this->paragraphsFromTextarea($old['history']['paragraphs']),
                        'quote' => $old['history']['quote'],
                        'secondary_title' => $old['history']['secondary_title'],
                        'secondary_text' => $old['history']['secondary_text'],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['mission'],
                ['settings_json' => null],
                [
                    'eyebrow' => $old['mission']['eyebrow'],
                    'heading' => $old['mission']['title'],
                    'body_json' => [
                        'items' => [
                            [
                                'symbol' => $old['mission']['item_one_symbol'],
                                'title' => $old['mission']['item_one_title'],
                                'description' => $old['mission']['item_one_description'],
                            ],
                            [
                                'symbol' => $old['mission']['item_two_symbol'],
                                'title' => $old['mission']['item_two_title'],
                                'description' => $old['mission']['item_two_description'],
                            ],
                            [
                                'symbol' => $old['mission']['item_three_symbol'],
                                'title' => $old['mission']['item_three_title'],
                                'description' => $old['mission']['item_three_description'],
                            ],
                        ],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['values'],
                [
                    'settings_json' => [
                        'feature_image_id' => $valuesFeatureMediaId,
                        'feature_image' => $old['values']['feature_image'],
                    ],
                ],
                [
                    'heading' => $old['values']['intro_title'],
                    'body_json' => [
                        'intro_text' => $old['values']['intro_text'],
                        'items' => [
                            [
                                'number' => $old['values']['item_one_number'],
                                'title' => $old['values']['item_one_title'],
                                'description' => $old['values']['item_one_description'],
                            ],
                            [
                                'number' => $old['values']['item_two_number'],
                                'title' => $old['values']['item_two_title'],
                                'description' => $old['values']['item_two_description'],
                            ],
                            [
                                'number' => $old['values']['item_three_number'],
                                'title' => $old['values']['item_three_title'],
                                'description' => $old['values']['item_three_description'],
                            ],
                            [
                                'number' => $old['values']['item_four_number'],
                                'title' => $old['values']['item_four_title'],
                                'description' => $old['values']['item_four_description'],
                            ],
                        ],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['facts'],
                ['settings_json' => null],
                [
                    'body_json' => [
                        'items' => [
                            ['value' => $old['facts']['fact_one_value'], 'label' => $old['facts']['fact_one_label']],
                            ['value' => $old['facts']['fact_two_value'], 'label' => $old['facts']['fact_two_label']],
                            ['value' => $old['facts']['fact_three_value'], 'label' => $old['facts']['fact_three_label']],
                            ['value' => $old['facts']['fact_four_value'], 'label' => $old['facts']['fact_four_label']],
                        ],
                    ],
                ]
            );

            $this->connection->commit();

            return ['ok' => true];
        } catch (Throwable) {
            $this->connection->rollBack();

            return ['ok' => false, 'errors' => ['The About page could not be saved right now.'], 'old' => $old];
        }
    }

    public function contactEditor(): ?array
    {
        $page = $this->pageWithSections('contact');

        if ($page === null) {
            return null;
        }

        $sections = $page['sections'];
        $hero = $sections['hero'] ?? [];
        $info = $sections['info'] ?? [];
        $infoBody = $info['body_json'] ?? [];
        $infoItems = array_values($infoBody['items'] ?? []);
        $socialLinks = array_values($infoBody['social_links'] ?? []);
        $form = $sections['form'] ?? [];
        $formBody = $form['body_json'] ?? [];
        $fields = array_values($formBody['fields'] ?? []);
        $map = $sections['map'] ?? [];
        $mapSettings = $map['settings'] ?? [];

        return [
            'page' => $this->pageMetaFromPage($page),
            'hero' => [
                'title' => (string) ($hero['heading'] ?? ''),
                'description' => (string) ($hero['body_long'] ?? ''),
                'image' => (string) ($hero['image_path'] ?? ''),
            ],
            'info' => [
                'eyebrow' => (string) ($info['eyebrow'] ?? ''),
                'title' => (string) ($info['heading'] ?? ''),
                'item_one_icon' => (string) (($infoItems[0]['icon'] ?? '')),
                'item_one_title' => (string) (($infoItems[0]['title'] ?? '')),
                'item_one_line_one_text' => (string) (($infoItems[0]['lines'][0]['text'] ?? '')),
                'item_one_line_one_href' => (string) (($infoItems[0]['lines'][0]['href'] ?? '')),
                'item_one_line_two_text' => (string) (($infoItems[0]['lines'][1]['text'] ?? '')),
                'item_one_line_two_href' => (string) (($infoItems[0]['lines'][1]['href'] ?? '')),
                'item_two_icon' => (string) (($infoItems[1]['icon'] ?? '')),
                'item_two_title' => (string) (($infoItems[1]['title'] ?? '')),
                'item_two_line_one_text' => (string) (($infoItems[1]['lines'][0]['text'] ?? '')),
                'item_two_line_two_text' => (string) (($infoItems[1]['lines'][1]['text'] ?? '')),
                'item_three_icon' => (string) (($infoItems[2]['icon'] ?? '')),
                'item_three_title' => (string) (($infoItems[2]['title'] ?? '')),
                'item_three_line_one_text' => (string) (($infoItems[2]['lines'][0]['text'] ?? '')),
                'item_three_line_one_href' => (string) (($infoItems[2]['lines'][0]['href'] ?? '')),
                'item_three_line_two_text' => (string) (($infoItems[2]['lines'][1]['text'] ?? '')),
                'item_three_line_two_href' => (string) (($infoItems[2]['lines'][1]['href'] ?? '')),
                'social_title' => (string) ($infoBody['social_title'] ?? ''),
                'social_one_label' => (string) (($socialLinks[0]['label'] ?? '')),
                'social_one_short' => (string) (($socialLinks[0]['short'] ?? '')),
                'social_one_href' => (string) (($socialLinks[0]['href'] ?? '')),
                'social_two_label' => (string) (($socialLinks[1]['label'] ?? '')),
                'social_two_short' => (string) (($socialLinks[1]['short'] ?? '')),
                'social_two_href' => (string) (($socialLinks[1]['href'] ?? '')),
                'social_three_label' => (string) (($socialLinks[2]['label'] ?? '')),
                'social_three_short' => (string) (($socialLinks[2]['short'] ?? '')),
                'social_three_href' => (string) (($socialLinks[2]['href'] ?? '')),
            ],
            'form' => [
                'title' => (string) ($form['heading'] ?? ''),
                'description' => (string) ($form['body_long'] ?? ''),
                'button' => (string) ($formBody['button'] ?? ''),
                'full_name_label' => (string) (($fields[0]['label'] ?? '')),
                'full_name_placeholder' => (string) (($fields[0]['placeholder'] ?? '')),
                'email_label' => (string) (($fields[1]['label'] ?? '')),
                'email_placeholder' => (string) (($fields[1]['placeholder'] ?? '')),
                'subject_label' => (string) (($fields[2]['label'] ?? '')),
                'subject_option_one' => (string) (($fields[2]['options'][0] ?? '')),
                'subject_option_two' => (string) (($fields[2]['options'][1] ?? '')),
                'subject_option_three' => (string) (($fields[2]['options'][2] ?? '')),
                'subject_option_four' => (string) (($fields[2]['options'][3] ?? '')),
                'message_label' => (string) (($fields[3]['label'] ?? '')),
                'message_placeholder' => (string) (($fields[3]['placeholder'] ?? '')),
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

    public function saveContact(array $input): array
    {
        $old = $this->contactFormFromInput($input);
        $errors = [];

        if (($old['page']['title'] ?? '') === '') {
            $errors[] = 'Please enter the internal page title.';
        }
        if (($old['hero']['title'] ?? '') === '') {
            $errors[] = 'Please enter the Contact hero title.';
        }
        if (($old['hero']['image'] ?? '') === '') {
            $errors[] = 'Please enter the Contact hero image path.';
        }
        if (($old['info']['title'] ?? '') === '') {
            $errors[] = 'Please enter the Contact info title.';
        }
        if (($old['map']['title'] ?? '') === '') {
            $errors[] = 'Please enter the map section title.';
        }
        if (($old['map']['image'] ?? '') === '') {
            $errors[] = 'Please enter the map image path.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'old' => $old];
        }

        $page = $this->pageWithSections('contact');

        if ($page === null) {
            return ['ok' => false, 'errors' => ['The Contact page could not be found in the database.'], 'old' => $old];
        }

        $this->connection->beginTransaction();

        try {
            $this->updatePageMeta($page['id'], $old['page']);

            $heroMediaId = $this->upsertMedia($old['hero']['image'], 'Contact Hero Artwork');
            $mapMediaId = $this->upsertMedia($old['map']['image'], 'Contact Map Artwork');

            $this->updateSection(
                $page['section_ids']['hero'],
                ['image_id' => $heroMediaId, 'settings_json' => null],
                [
                    'heading' => $old['hero']['title'],
                    'body_long' => $old['hero']['description'],
                ]
            );

            $this->updateSection(
                $page['section_ids']['info'],
                ['settings_json' => null],
                [
                    'eyebrow' => $old['info']['eyebrow'],
                    'heading' => $old['info']['title'],
                    'body_json' => [
                        'items' => [
                            [
                                'icon' => $old['info']['item_one_icon'],
                                'title' => $old['info']['item_one_title'],
                                'lines' => [
                                    ['text' => $old['info']['item_one_line_one_text'], 'href' => $old['info']['item_one_line_one_href']],
                                    ['text' => $old['info']['item_one_line_two_text'], 'href' => $old['info']['item_one_line_two_href']],
                                ],
                            ],
                            [
                                'icon' => $old['info']['item_two_icon'],
                                'title' => $old['info']['item_two_title'],
                                'lines' => [
                                    ['text' => $old['info']['item_two_line_one_text']],
                                    ['text' => $old['info']['item_two_line_two_text']],
                                ],
                            ],
                            [
                                'icon' => $old['info']['item_three_icon'],
                                'title' => $old['info']['item_three_title'],
                                'lines' => [
                                    ['text' => $old['info']['item_three_line_one_text'], 'href' => $old['info']['item_three_line_one_href']],
                                    ['text' => $old['info']['item_three_line_two_text'], 'href' => $old['info']['item_three_line_two_href']],
                                ],
                            ],
                        ],
                        'social_title' => $old['info']['social_title'],
                        'social_links' => [
                            ['label' => $old['info']['social_one_label'], 'short' => $old['info']['social_one_short'], 'href' => $old['info']['social_one_href']],
                            ['label' => $old['info']['social_two_label'], 'short' => $old['info']['social_two_short'], 'href' => $old['info']['social_two_href']],
                            ['label' => $old['info']['social_three_label'], 'short' => $old['info']['social_three_short'], 'href' => $old['info']['social_three_href']],
                        ],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['form'],
                ['settings_json' => null],
                [
                    'heading' => $old['form']['title'],
                    'body_long' => $old['form']['description'],
                    'body_json' => [
                        'fields' => [
                            [
                                'label' => $old['form']['full_name_label'],
                                'name' => 'full_name',
                                'type' => 'text',
                                'placeholder' => $old['form']['full_name_placeholder'],
                                'width' => 'half',
                            ],
                            [
                                'label' => $old['form']['email_label'],
                                'name' => 'email',
                                'type' => 'email',
                                'placeholder' => $old['form']['email_placeholder'],
                                'width' => 'half',
                            ],
                            [
                                'label' => $old['form']['subject_label'],
                                'name' => 'subject',
                                'type' => 'select',
                                'width' => 'full',
                                'options' => [
                                    $old['form']['subject_option_one'],
                                    $old['form']['subject_option_two'],
                                    $old['form']['subject_option_three'],
                                    $old['form']['subject_option_four'],
                                ],
                            ],
                            [
                                'label' => $old['form']['message_label'],
                                'name' => 'message',
                                'type' => 'textarea',
                                'placeholder' => $old['form']['message_placeholder'],
                                'width' => 'full',
                            ],
                        ],
                        'button' => $old['form']['button'],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['map'],
                [
                    'image_id' => $mapMediaId,
                    'settings_json' => [
                        'cta' => $old['map']['cta'],
                        'href' => $old['map']['href'],
                        'marker' => $old['map']['marker'],
                    ],
                ],
                [
                    'eyebrow' => $old['map']['eyebrow'],
                    'heading' => $old['map']['title'],
                ]
            );

            $this->connection->commit();

            return ['ok' => true];
        } catch (Throwable) {
            $this->connection->rollBack();

            return ['ok' => false, 'errors' => ['The Contact page could not be saved right now.'], 'old' => $old];
        }
    }

    public function donationsEditor(): ?array
    {
        $page = $this->pageWithSections('donations');

        if ($page === null) {
            return null;
        }

        $sections = $page['sections'];
        $hero = $sections['hero'] ?? [];
        $heroBody = $hero['body_json'] ?? [];
        $heroSettings = $hero['settings'] ?? [];
        $impact = $heroBody['impact'] ?? [];
        $transparency = $heroSettings['transparency'] ?? [];
        $methods = $sections['methods'] ?? [];
        $methodItems = array_values(($methods['body_json']['items'] ?? []));
        $instructions = $sections['instructions'] ?? [];
        $instructionsBody = $instructions['body_json'] ?? [];
        $instructionItems = array_values($instructionsBody['items'] ?? []);
        $benefit = $instructionsBody['benefit'] ?? [];
        $form = $sections['form'] ?? [];
        $formBody = $form['body_json'] ?? [];
        $fields = array_values($formBody['fields'] ?? []);
        $paymentOptions = array_values(($fields[1]['options'] ?? []));

        return [
            'page' => $this->pageMetaFromPage($page),
            'hero' => [
                'eyebrow' => (string) ($hero['eyebrow'] ?? ''),
                'title_prefix' => (string) ($hero['heading'] ?? ''),
                'title_highlight' => (string) ($hero['subheading'] ?? ''),
                'description' => (string) ($hero['body_long'] ?? ''),
                'impact_title' => (string) ($impact['title'] ?? ''),
                'impact_description' => (string) ($impact['description'] ?? ''),
                'hero_image' => (string) ($hero['image_path'] ?? ''),
                'transparency_stat' => (string) ($transparency['stat'] ?? ''),
                'transparency_label' => (string) ($transparency['label'] ?? ''),
            ],
            'methods' => [
                'title' => (string) ($methods['heading'] ?? ''),
                'method_one_icon' => (string) (($methodItems[0]['icon'] ?? '')),
                'method_one_tone' => (string) (($methodItems[0]['tone'] ?? '')),
                'method_one_title' => (string) (($methodItems[0]['title'] ?? '')),
                'method_one_description' => (string) (($methodItems[0]['description'] ?? '')),
                'method_one_form_one' => (string) (($methodItems[0]['accepted_forms'][0] ?? '')),
                'method_one_form_two' => (string) (($methodItems[0]['accepted_forms'][1] ?? '')),
                'method_one_form_three' => (string) (($methodItems[0]['accepted_forms'][2] ?? '')),
                'method_one_form_four' => (string) (($methodItems[0]['accepted_forms'][3] ?? '')),
                'method_two_icon' => (string) (($methodItems[1]['icon'] ?? '')),
                'method_two_tone' => (string) (($methodItems[1]['tone'] ?? '')),
                'method_two_title' => (string) (($methodItems[1]['title'] ?? '')),
                'method_two_description' => (string) (($methodItems[1]['description'] ?? '')),
                'method_two_code' => (string) (($methodItems[1]['code'] ?? '')),
                'method_three_icon' => (string) (($methodItems[2]['icon'] ?? '')),
                'method_three_tone' => (string) (($methodItems[2]['tone'] ?? '')),
                'method_three_title' => (string) (($methodItems[2]['title'] ?? '')),
                'method_three_description' => (string) (($methodItems[2]['description'] ?? '')),
                'method_three_detail_one_label' => (string) (($methodItems[2]['details'][0]['label'] ?? '')),
                'method_three_detail_one_value' => (string) (($methodItems[2]['details'][0]['value'] ?? '')),
                'method_three_detail_two_label' => (string) (($methodItems[2]['details'][1]['label'] ?? '')),
                'method_three_detail_two_value' => (string) (($methodItems[2]['details'][1]['value'] ?? '')),
                'method_three_detail_three_label' => (string) (($methodItems[2]['details'][2]['label'] ?? '')),
                'method_three_detail_three_value' => (string) (($methodItems[2]['details'][2]['value'] ?? '')),
            ],
            'instructions' => [
                'title' => (string) ($instructions['heading'] ?? ''),
                'item_one_number' => (string) (($instructionItems[0]['number'] ?? '')),
                'item_one_title' => (string) (($instructionItems[0]['title'] ?? '')),
                'item_one_description' => (string) (($instructionItems[0]['description'] ?? '')),
                'item_two_number' => (string) (($instructionItems[1]['number'] ?? '')),
                'item_two_title' => (string) (($instructionItems[1]['title'] ?? '')),
                'item_two_description' => (string) (($instructionItems[1]['description'] ?? '')),
                'item_three_number' => (string) (($instructionItems[2]['number'] ?? '')),
                'item_three_title' => (string) (($instructionItems[2]['title'] ?? '')),
                'item_three_description' => (string) (($instructionItems[2]['description'] ?? '')),
                'benefit_title' => (string) ($benefit['title'] ?? ''),
                'benefit_description' => (string) ($benefit['description'] ?? ''),
            ],
            'form' => [
                'title' => (string) ($form['heading'] ?? ''),
                'description' => (string) ($form['body_long'] ?? ''),
                'button' => (string) ($formBody['button'] ?? ''),
                'reference_note' => (string) ($formBody['reference_note'] ?? ''),
                'full_name_label' => (string) (($fields[0]['label'] ?? '')),
                'full_name_placeholder' => (string) (($fields[0]['placeholder'] ?? '')),
                'payment_label' => (string) (($fields[1]['label'] ?? '')),
                'payment_option_one_label' => (string) (($paymentOptions[0]['label'] ?? '')),
                'payment_option_two_label' => (string) (($paymentOptions[1]['label'] ?? '')),
                'payment_option_three_label' => (string) (($paymentOptions[2]['label'] ?? '')),
                'amount_label' => (string) (($fields[2]['label'] ?? '')),
                'amount_placeholder' => (string) (($fields[2]['placeholder'] ?? '')),
                'reference_label' => (string) (($fields[3]['label'] ?? '')),
                'reference_placeholder' => (string) (($fields[3]['placeholder'] ?? '')),
                'phone_label' => (string) (($fields[4]['label'] ?? '')),
                'phone_placeholder' => (string) (($fields[4]['placeholder'] ?? '')),
                'email_label' => (string) (($fields[5]['label'] ?? '')),
                'email_placeholder' => (string) (($fields[5]['placeholder'] ?? '')),
                'address_label' => (string) (($fields[6]['label'] ?? '')),
                'address_placeholder' => (string) (($fields[6]['placeholder'] ?? '')),
                'message_label' => (string) (($fields[7]['label'] ?? '')),
                'message_placeholder' => (string) (($fields[7]['placeholder'] ?? '')),
            ],
        ];
    }

    public function saveDonations(array $input): array
    {
        $old = $this->donationsFormFromInput($input);
        $errors = [];

        if (($old['page']['title'] ?? '') === '') {
            $errors[] = 'Please enter the internal page title.';
        }
        if (($old['hero']['title_prefix'] ?? '') === '') {
            $errors[] = 'Please enter the Donations hero title.';
        }
        if (($old['hero']['hero_image'] ?? '') === '') {
            $errors[] = 'Please enter the Donations hero image path.';
        }
        if (($old['methods']['title'] ?? '') === '') {
            $errors[] = 'Please enter the donation methods title.';
        }
        if (($old['instructions']['title'] ?? '') === '') {
            $errors[] = 'Please enter the donation instructions title.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'old' => $old];
        }

        $page = $this->pageWithSections('donations');

        if ($page === null) {
            return ['ok' => false, 'errors' => ['The Donations page could not be found in the database.'], 'old' => $old];
        }

        $this->connection->beginTransaction();

        try {
            $this->updatePageMeta($page['id'], $old['page']);

            $heroMediaId = $this->upsertMedia($old['hero']['hero_image'], 'Donations Hero Artwork');

            $this->updateSection(
                $page['section_ids']['hero'],
                [
                    'image_id' => $heroMediaId,
                    'settings_json' => [
                        'transparency' => [
                            'stat' => $old['hero']['transparency_stat'],
                            'label' => $old['hero']['transparency_label'],
                        ],
                    ],
                ],
                [
                    'eyebrow' => $old['hero']['eyebrow'],
                    'heading' => $old['hero']['title_prefix'],
                    'subheading' => $old['hero']['title_highlight'],
                    'body_long' => $old['hero']['description'],
                    'body_json' => [
                        'impact' => [
                            'title' => $old['hero']['impact_title'],
                            'description' => $old['hero']['impact_description'],
                        ],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['methods'],
                ['settings_json' => null],
                [
                    'heading' => $old['methods']['title'],
                    'body_json' => [
                        'items' => [
                            [
                                'icon' => $old['methods']['method_one_icon'],
                                'tone' => $old['methods']['method_one_tone'],
                                'title' => $old['methods']['method_one_title'],
                                'description' => $old['methods']['method_one_description'],
                                'type' => 'accepted_forms',
                                'accepted_forms' => [
                                    $old['methods']['method_one_form_one'],
                                    $old['methods']['method_one_form_two'],
                                    $old['methods']['method_one_form_three'],
                                    $old['methods']['method_one_form_four'],
                                ],
                            ],
                            [
                                'icon' => $old['methods']['method_two_icon'],
                                'tone' => $old['methods']['method_two_tone'],
                                'title' => $old['methods']['method_two_title'],
                                'description' => $old['methods']['method_two_description'],
                                'type' => 'code',
                                'code' => $old['methods']['method_two_code'],
                            ],
                            [
                                'icon' => $old['methods']['method_three_icon'],
                                'tone' => $old['methods']['method_three_tone'],
                                'title' => $old['methods']['method_three_title'],
                                'description' => $old['methods']['method_three_description'],
                                'type' => 'details',
                                'details' => [
                                    ['label' => $old['methods']['method_three_detail_one_label'], 'value' => $old['methods']['method_three_detail_one_value']],
                                    ['label' => $old['methods']['method_three_detail_two_label'], 'value' => $old['methods']['method_three_detail_two_value']],
                                    ['label' => $old['methods']['method_three_detail_three_label'], 'value' => $old['methods']['method_three_detail_three_value']],
                                ],
                            ],
                        ],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['instructions'],
                ['settings_json' => null],
                [
                    'heading' => $old['instructions']['title'],
                    'body_json' => [
                        'items' => [
                            ['number' => $old['instructions']['item_one_number'], 'title' => $old['instructions']['item_one_title'], 'description' => $old['instructions']['item_one_description']],
                            ['number' => $old['instructions']['item_two_number'], 'title' => $old['instructions']['item_two_title'], 'description' => $old['instructions']['item_two_description']],
                            ['number' => $old['instructions']['item_three_number'], 'title' => $old['instructions']['item_three_title'], 'description' => $old['instructions']['item_three_description']],
                        ],
                        'benefit' => [
                            'title' => $old['instructions']['benefit_title'],
                            'description' => $old['instructions']['benefit_description'],
                        ],
                    ],
                ]
            );

            $this->updateSection(
                $page['section_ids']['form'],
                ['settings_json' => null],
                [
                    'heading' => $old['form']['title'],
                    'body_long' => $old['form']['description'],
                    'body_json' => [
                        'fields' => [
                            ['label' => $old['form']['full_name_label'], 'name' => 'full_name', 'type' => 'text', 'placeholder' => $old['form']['full_name_placeholder'], 'width' => 'half'],
                            [
                                'label' => $old['form']['payment_label'],
                                'name' => 'payment_method',
                                'type' => 'select',
                                'width' => 'half',
                                'options' => [
                                    ['value' => 'temple-offline', 'label' => $old['form']['payment_option_one_label']],
                                    ['value' => 'upi-transfer', 'label' => $old['form']['payment_option_two_label']],
                                    ['value' => 'bank-transfer', 'label' => $old['form']['payment_option_three_label']],
                                ],
                            ],
                            ['label' => $old['form']['amount_label'], 'name' => 'amount', 'type' => 'text', 'placeholder' => $old['form']['amount_placeholder'], 'width' => 'half'],
                            ['label' => $old['form']['reference_label'], 'name' => 'reference_id', 'type' => 'text', 'placeholder' => $old['form']['reference_placeholder'], 'width' => 'half'],
                            ['label' => $old['form']['phone_label'], 'name' => 'phone', 'type' => 'tel', 'placeholder' => $old['form']['phone_placeholder'], 'width' => 'half'],
                            ['label' => $old['form']['email_label'], 'name' => 'email', 'type' => 'email', 'placeholder' => $old['form']['email_placeholder'], 'width' => 'half'],
                            ['label' => $old['form']['address_label'], 'name' => 'address', 'type' => 'text', 'placeholder' => $old['form']['address_placeholder'], 'width' => 'full'],
                            ['label' => $old['form']['message_label'], 'name' => 'message', 'type' => 'textarea', 'placeholder' => $old['form']['message_placeholder'], 'width' => 'full'],
                        ],
                        'reference_note' => $old['form']['reference_note'],
                        'button' => $old['form']['button'],
                    ],
                ]
            );

            $this->connection->commit();

            return ['ok' => true];
        } catch (Throwable) {
            $this->connection->rollBack();

            return ['ok' => false, 'errors' => ['The Donations page could not be saved right now.'], 'old' => $old];
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

    private function updatePageMeta(int $pageId, array $pageData): void
    {
        $pageUpdate = $this->connection->prepare(
            'UPDATE page_translations
             SET title = :title,
                 meta_title = :meta_title,
                 meta_description = :meta_description
             WHERE page_id = :page_id AND locale_id = :locale_id'
        );
        $pageUpdate->execute([
            'title' => $pageData['title'] ?? '',
            'meta_title' => $pageData['meta_title'] ?? '',
            'meta_description' => $pageData['meta_description'] ?? '',
            'page_id' => $pageId,
            'locale_id' => $this->localeId(),
        ]);
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

    private function pageMetaFromPage(array $page): array
    {
        return [
            'title' => (string) ($page['title'] ?? ''),
            'meta_title' => (string) ($page['meta_title'] ?? ''),
            'meta_description' => (string) ($page['meta_description'] ?? ''),
            'status' => (string) ($page['status'] ?? 'published'),
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
            'page' => $this->pageMetaFromInput($input),
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

    private function aboutFormFromInput(array $input): array
    {
        return [
            'page' => $this->pageMetaFromInput($input),
            'hero' => [
                'eyebrow' => trim((string) ($input['hero_eyebrow'] ?? '')),
                'title' => trim((string) ($input['hero_title'] ?? '')),
                'highlight' => trim((string) ($input['hero_highlight'] ?? '')),
                'image' => trim((string) ($input['hero_image'] ?? '')),
            ],
            'history' => [
                'eyebrow' => trim((string) ($input['history_eyebrow'] ?? '')),
                'title' => trim((string) ($input['history_title'] ?? '')),
                'paragraphs' => trim((string) ($input['history_paragraphs'] ?? '')),
                'quote' => trim((string) ($input['history_quote'] ?? '')),
                'main_image' => trim((string) ($input['history_main_image'] ?? '')),
                'secondary_image' => trim((string) ($input['history_secondary_image'] ?? '')),
                'secondary_title' => trim((string) ($input['history_secondary_title'] ?? '')),
                'secondary_text' => trim((string) ($input['history_secondary_text'] ?? '')),
            ],
            'mission' => [
                'eyebrow' => trim((string) ($input['mission_eyebrow'] ?? '')),
                'title' => trim((string) ($input['mission_title'] ?? '')),
                'item_one_symbol' => trim((string) ($input['mission_item_one_symbol'] ?? '')),
                'item_one_title' => trim((string) ($input['mission_item_one_title'] ?? '')),
                'item_one_description' => trim((string) ($input['mission_item_one_description'] ?? '')),
                'item_two_symbol' => trim((string) ($input['mission_item_two_symbol'] ?? '')),
                'item_two_title' => trim((string) ($input['mission_item_two_title'] ?? '')),
                'item_two_description' => trim((string) ($input['mission_item_two_description'] ?? '')),
                'item_three_symbol' => trim((string) ($input['mission_item_three_symbol'] ?? '')),
                'item_three_title' => trim((string) ($input['mission_item_three_title'] ?? '')),
                'item_three_description' => trim((string) ($input['mission_item_three_description'] ?? '')),
            ],
            'values' => [
                'intro_title' => trim((string) ($input['values_intro_title'] ?? '')),
                'intro_text' => trim((string) ($input['values_intro_text'] ?? '')),
                'feature_image' => trim((string) ($input['values_feature_image'] ?? '')),
                'item_one_number' => trim((string) ($input['values_item_one_number'] ?? '')),
                'item_one_title' => trim((string) ($input['values_item_one_title'] ?? '')),
                'item_one_description' => trim((string) ($input['values_item_one_description'] ?? '')),
                'item_two_number' => trim((string) ($input['values_item_two_number'] ?? '')),
                'item_two_title' => trim((string) ($input['values_item_two_title'] ?? '')),
                'item_two_description' => trim((string) ($input['values_item_two_description'] ?? '')),
                'item_three_number' => trim((string) ($input['values_item_three_number'] ?? '')),
                'item_three_title' => trim((string) ($input['values_item_three_title'] ?? '')),
                'item_three_description' => trim((string) ($input['values_item_three_description'] ?? '')),
                'item_four_number' => trim((string) ($input['values_item_four_number'] ?? '')),
                'item_four_title' => trim((string) ($input['values_item_four_title'] ?? '')),
                'item_four_description' => trim((string) ($input['values_item_four_description'] ?? '')),
            ],
            'facts' => [
                'fact_one_value' => trim((string) ($input['fact_one_value'] ?? '')),
                'fact_one_label' => trim((string) ($input['fact_one_label'] ?? '')),
                'fact_two_value' => trim((string) ($input['fact_two_value'] ?? '')),
                'fact_two_label' => trim((string) ($input['fact_two_label'] ?? '')),
                'fact_three_value' => trim((string) ($input['fact_three_value'] ?? '')),
                'fact_three_label' => trim((string) ($input['fact_three_label'] ?? '')),
                'fact_four_value' => trim((string) ($input['fact_four_value'] ?? '')),
                'fact_four_label' => trim((string) ($input['fact_four_label'] ?? '')),
            ],
        ];
    }

    private function contactFormFromInput(array $input): array
    {
        return [
            'page' => $this->pageMetaFromInput($input),
            'hero' => [
                'title' => trim((string) ($input['hero_title'] ?? '')),
                'description' => trim((string) ($input['hero_description'] ?? '')),
                'image' => trim((string) ($input['hero_image'] ?? '')),
            ],
            'info' => [
                'eyebrow' => trim((string) ($input['info_eyebrow'] ?? '')),
                'title' => trim((string) ($input['info_title'] ?? '')),
                'item_one_icon' => trim((string) ($input['info_item_one_icon'] ?? '')),
                'item_one_title' => trim((string) ($input['info_item_one_title'] ?? '')),
                'item_one_line_one_text' => trim((string) ($input['info_item_one_line_one_text'] ?? '')),
                'item_one_line_one_href' => trim((string) ($input['info_item_one_line_one_href'] ?? '')),
                'item_one_line_two_text' => trim((string) ($input['info_item_one_line_two_text'] ?? '')),
                'item_one_line_two_href' => trim((string) ($input['info_item_one_line_two_href'] ?? '')),
                'item_two_icon' => trim((string) ($input['info_item_two_icon'] ?? '')),
                'item_two_title' => trim((string) ($input['info_item_two_title'] ?? '')),
                'item_two_line_one_text' => trim((string) ($input['info_item_two_line_one_text'] ?? '')),
                'item_two_line_two_text' => trim((string) ($input['info_item_two_line_two_text'] ?? '')),
                'item_three_icon' => trim((string) ($input['info_item_three_icon'] ?? '')),
                'item_three_title' => trim((string) ($input['info_item_three_title'] ?? '')),
                'item_three_line_one_text' => trim((string) ($input['info_item_three_line_one_text'] ?? '')),
                'item_three_line_one_href' => trim((string) ($input['info_item_three_line_one_href'] ?? '')),
                'item_three_line_two_text' => trim((string) ($input['info_item_three_line_two_text'] ?? '')),
                'item_three_line_two_href' => trim((string) ($input['info_item_three_line_two_href'] ?? '')),
                'social_title' => trim((string) ($input['social_title'] ?? '')),
                'social_one_label' => trim((string) ($input['social_one_label'] ?? '')),
                'social_one_short' => trim((string) ($input['social_one_short'] ?? '')),
                'social_one_href' => trim((string) ($input['social_one_href'] ?? '')),
                'social_two_label' => trim((string) ($input['social_two_label'] ?? '')),
                'social_two_short' => trim((string) ($input['social_two_short'] ?? '')),
                'social_two_href' => trim((string) ($input['social_two_href'] ?? '')),
                'social_three_label' => trim((string) ($input['social_three_label'] ?? '')),
                'social_three_short' => trim((string) ($input['social_three_short'] ?? '')),
                'social_three_href' => trim((string) ($input['social_three_href'] ?? '')),
            ],
            'form' => [
                'title' => trim((string) ($input['form_title'] ?? '')),
                'description' => trim((string) ($input['form_description'] ?? '')),
                'button' => trim((string) ($input['form_button'] ?? '')),
                'full_name_label' => trim((string) ($input['form_full_name_label'] ?? '')),
                'full_name_placeholder' => trim((string) ($input['form_full_name_placeholder'] ?? '')),
                'email_label' => trim((string) ($input['form_email_label'] ?? '')),
                'email_placeholder' => trim((string) ($input['form_email_placeholder'] ?? '')),
                'subject_label' => trim((string) ($input['form_subject_label'] ?? '')),
                'subject_option_one' => trim((string) ($input['form_subject_option_one'] ?? '')),
                'subject_option_two' => trim((string) ($input['form_subject_option_two'] ?? '')),
                'subject_option_three' => trim((string) ($input['form_subject_option_three'] ?? '')),
                'subject_option_four' => trim((string) ($input['form_subject_option_four'] ?? '')),
                'message_label' => trim((string) ($input['form_message_label'] ?? '')),
                'message_placeholder' => trim((string) ($input['form_message_placeholder'] ?? '')),
            ],
            'map' => [
                'eyebrow' => trim((string) ($input['map_eyebrow'] ?? '')),
                'title' => trim((string) ($input['map_title'] ?? '')),
                'cta' => trim((string) ($input['map_cta'] ?? '')),
                'href' => trim((string) ($input['map_href'] ?? '')),
                'image' => trim((string) ($input['map_image'] ?? '')),
                'marker' => trim((string) ($input['map_marker'] ?? '')),
            ],
        ];
    }

    private function donationsFormFromInput(array $input): array
    {
        return [
            'page' => $this->pageMetaFromInput($input),
            'hero' => [
                'eyebrow' => trim((string) ($input['hero_eyebrow'] ?? '')),
                'title_prefix' => trim((string) ($input['hero_title_prefix'] ?? '')),
                'title_highlight' => trim((string) ($input['hero_title_highlight'] ?? '')),
                'description' => trim((string) ($input['hero_description'] ?? '')),
                'impact_title' => trim((string) ($input['hero_impact_title'] ?? '')),
                'impact_description' => trim((string) ($input['hero_impact_description'] ?? '')),
                'hero_image' => trim((string) ($input['hero_image'] ?? '')),
                'transparency_stat' => trim((string) ($input['hero_transparency_stat'] ?? '')),
                'transparency_label' => trim((string) ($input['hero_transparency_label'] ?? '')),
            ],
            'methods' => [
                'title' => trim((string) ($input['methods_title'] ?? '')),
                'method_one_icon' => trim((string) ($input['method_one_icon'] ?? '')),
                'method_one_tone' => trim((string) ($input['method_one_tone'] ?? '')),
                'method_one_title' => trim((string) ($input['method_one_title'] ?? '')),
                'method_one_description' => trim((string) ($input['method_one_description'] ?? '')),
                'method_one_form_one' => trim((string) ($input['method_one_form_one'] ?? '')),
                'method_one_form_two' => trim((string) ($input['method_one_form_two'] ?? '')),
                'method_one_form_three' => trim((string) ($input['method_one_form_three'] ?? '')),
                'method_one_form_four' => trim((string) ($input['method_one_form_four'] ?? '')),
                'method_two_icon' => trim((string) ($input['method_two_icon'] ?? '')),
                'method_two_tone' => trim((string) ($input['method_two_tone'] ?? '')),
                'method_two_title' => trim((string) ($input['method_two_title'] ?? '')),
                'method_two_description' => trim((string) ($input['method_two_description'] ?? '')),
                'method_two_code' => trim((string) ($input['method_two_code'] ?? '')),
                'method_three_icon' => trim((string) ($input['method_three_icon'] ?? '')),
                'method_three_tone' => trim((string) ($input['method_three_tone'] ?? '')),
                'method_three_title' => trim((string) ($input['method_three_title'] ?? '')),
                'method_three_description' => trim((string) ($input['method_three_description'] ?? '')),
                'method_three_detail_one_label' => trim((string) ($input['method_three_detail_one_label'] ?? '')),
                'method_three_detail_one_value' => trim((string) ($input['method_three_detail_one_value'] ?? '')),
                'method_three_detail_two_label' => trim((string) ($input['method_three_detail_two_label'] ?? '')),
                'method_three_detail_two_value' => trim((string) ($input['method_three_detail_two_value'] ?? '')),
                'method_three_detail_three_label' => trim((string) ($input['method_three_detail_three_label'] ?? '')),
                'method_three_detail_three_value' => trim((string) ($input['method_three_detail_three_value'] ?? '')),
            ],
            'instructions' => [
                'title' => trim((string) ($input['instructions_title'] ?? '')),
                'item_one_number' => trim((string) ($input['instructions_item_one_number'] ?? '')),
                'item_one_title' => trim((string) ($input['instructions_item_one_title'] ?? '')),
                'item_one_description' => trim((string) ($input['instructions_item_one_description'] ?? '')),
                'item_two_number' => trim((string) ($input['instructions_item_two_number'] ?? '')),
                'item_two_title' => trim((string) ($input['instructions_item_two_title'] ?? '')),
                'item_two_description' => trim((string) ($input['instructions_item_two_description'] ?? '')),
                'item_three_number' => trim((string) ($input['instructions_item_three_number'] ?? '')),
                'item_three_title' => trim((string) ($input['instructions_item_three_title'] ?? '')),
                'item_three_description' => trim((string) ($input['instructions_item_three_description'] ?? '')),
                'benefit_title' => trim((string) ($input['benefit_title'] ?? '')),
                'benefit_description' => trim((string) ($input['benefit_description'] ?? '')),
            ],
            'form' => [
                'title' => trim((string) ($input['form_title'] ?? '')),
                'description' => trim((string) ($input['form_description'] ?? '')),
                'button' => trim((string) ($input['form_button'] ?? '')),
                'reference_note' => trim((string) ($input['form_reference_note'] ?? '')),
                'full_name_label' => trim((string) ($input['form_full_name_label'] ?? '')),
                'full_name_placeholder' => trim((string) ($input['form_full_name_placeholder'] ?? '')),
                'payment_label' => trim((string) ($input['form_payment_label'] ?? '')),
                'payment_option_one_label' => trim((string) ($input['form_payment_option_one_label'] ?? '')),
                'payment_option_two_label' => trim((string) ($input['form_payment_option_two_label'] ?? '')),
                'payment_option_three_label' => trim((string) ($input['form_payment_option_three_label'] ?? '')),
                'amount_label' => trim((string) ($input['form_amount_label'] ?? '')),
                'amount_placeholder' => trim((string) ($input['form_amount_placeholder'] ?? '')),
                'reference_label' => trim((string) ($input['form_reference_label'] ?? '')),
                'reference_placeholder' => trim((string) ($input['form_reference_placeholder'] ?? '')),
                'phone_label' => trim((string) ($input['form_phone_label'] ?? '')),
                'phone_placeholder' => trim((string) ($input['form_phone_placeholder'] ?? '')),
                'email_label' => trim((string) ($input['form_email_label'] ?? '')),
                'email_placeholder' => trim((string) ($input['form_email_placeholder'] ?? '')),
                'address_label' => trim((string) ($input['form_address_label'] ?? '')),
                'address_placeholder' => trim((string) ($input['form_address_placeholder'] ?? '')),
                'message_label' => trim((string) ($input['form_message_label'] ?? '')),
                'message_placeholder' => trim((string) ($input['form_message_placeholder'] ?? '')),
            ],
        ];
    }

    private function pageMetaFromInput(array $input): array
    {
        return [
            'title' => trim((string) ($input['page_title'] ?? '')),
            'meta_title' => trim((string) ($input['meta_title'] ?? '')),
            'meta_description' => trim((string) ($input['meta_description'] ?? '')),
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
