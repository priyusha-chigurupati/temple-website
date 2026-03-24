<?php

declare(strict_types=1);

return [
    'site' => [
        'name' => 'AnkammaThalli Temple',
        'tagline' => 'A timeless sanctuary of grace and tradition.',
        'locale' => 'en_US',
        'admin_link' => '/admin',
    ],
    'navigation' => [
        ['label' => 'Home', 'href' => '/'],
        ['label' => 'About', 'href' => '/about'],
        ['label' => 'Gallery', 'href' => '/gallery'],
        ['label' => 'Events', 'href' => '/events'],
        ['label' => 'Donations', 'href' => '/donations'],
        ['label' => 'Contact', 'href' => '/contact'],
        ['label' => 'Blog', 'href' => '/blog'],
    ],
    'footer' => [
        'description' => 'A sacred place for devotion, service, and shared heritage. Sample content is being used during the build phase.',
        'quick_links' => [
            ['label' => 'Pooja Timings', 'href' => '/events'],
            ['label' => 'Temple History', 'href' => '/about'],
            ['label' => 'Festival Calendar', 'href' => '/events'],
            ['label' => 'Contact Us', 'href' => '/contact'],
        ],
        'address' => [
            'lines' => [
                '123 Temple Road',
                'Heritage District',
                'Sacred Valley, AP 522001',
            ],
            'morning' => '6:00 AM - 12:30 PM',
            'evening' => '4:00 PM - 8:30 PM',
        ],
        'newsletter' => [
            'title' => 'Newsletter',
            'description' => 'Receive festival announcements and community updates.',
        ],
        'social_links' => [
            ['label' => 'Facebook', 'href' => '#', 'short' => 'f'],
            ['label' => 'Instagram', 'href' => '#', 'short' => 'ig'],
            ['label' => 'YouTube', 'href' => '#', 'short' => 'yt'],
        ],
        'legal' => [
            ['label' => 'Privacy Policy', 'href' => '#'],
            ['label' => 'Terms of Service', 'href' => '#'],
        ],
    ],
    'pages' => [
        'home' => [
            'meta' => [
                'title' => 'Home | AnkammaThalli Temple',
                'description' => 'Welcome to AnkammaThalli Temple. Explore temple heritage, events, gallery highlights, and ways to support the sanctuary.',
            ],
            'hero' => [
                'eyebrow' => 'Centuries of Devotion',
                'title_prefix' => 'Welcome to',
                'title_highlight' => 'AnkammaThalli',
                'title_suffix' => 'Temple',
                'description' => 'Step into a sanctuary of spiritual resonance where heritage, community, and quiet devotion meet in a modern digital presence.',
                'primary_cta' => ['label' => 'Support the Temple', 'href' => '/donations'],
                'secondary_cta' => ['label' => 'View Timings', 'href' => '/contact'],
                'background_image' => 'assets/images/placeholders/hero-veil.svg',
                'feature_image' => 'assets/images/placeholders/sanctum-disc.svg',
            ],
            'about_section' => [
                'title' => 'Sacred History & Architecture',
                'description' => [
                    'Founded as a place of prayer and community gathering, the temple continues to hold memory, ritual, and craftsmanship at its center.',
                    'For this first milestone we are using sample text, but the layout is prepared so every sentence, heading, and image can later come from the admin-managed database.',
                ],
                'cta' => ['label' => 'Read our full story', 'href' => '/about'],
                'image' => 'assets/images/placeholders/architecture-tower.svg',
            ],
            'events_section' => [
                'eyebrow' => 'Community & Worship',
                'title' => 'Upcoming Events',
                'cta' => ['label' => 'View All Calendar', 'href' => '/events'],
                'items' => [
                    [
                        'date' => 'Apr 14, 2026',
                        'title' => 'Annual Maha Utsav',
                        'description' => 'A three-day celebration with devotional music, floral decorations, and special puja ceremonies.',
                        'href' => '/events',
                        'image' => 'assets/images/placeholders/event-lamps.svg',
                    ],
                    [
                        'date' => 'Apr 22, 2026',
                        'title' => 'Maha Poornima Ritual',
                        'description' => 'An evening gathering for lamp lighting, chanting, and shared prayers under the full moon.',
                        'href' => '/events',
                        'image' => 'assets/images/placeholders/event-deity.svg',
                    ],
                    [
                        'date' => 'May 03, 2026',
                        'title' => 'Spiritual Discourse',
                        'description' => 'A guided session on temple symbolism, oral tradition, and the spirit of the divine mother.',
                        'href' => '/events',
                        'image' => 'assets/images/placeholders/event-stone.svg',
                    ],
                ],
            ],
            'gallery_section' => [
                'title' => 'Temple Highlights',
                'cta' => ['label' => 'Explore Gallery', 'href' => '/gallery'],
                'items' => [
                    ['label' => 'The Golden Gopuram', 'image' => 'assets/images/placeholders/highlight-hall.svg'],
                    ['label' => 'Ritual Lamps', 'image' => 'assets/images/placeholders/highlight-lamps.svg'],
                    ['label' => 'Courtyard Greens', 'image' => 'assets/images/placeholders/highlight-greenery.svg'],
                    ['label' => 'Sacred Textures', 'image' => 'assets/images/placeholders/highlight-texture.svg'],
                ],
            ],
            'donation_section' => [
                'title' => 'Support the Sacred Sanctuary',
                'description' => 'Sample donation blocks are in place so the real temple funds, campaigns, and instructions can later be managed through the backend.',
                'cards' => [
                    [
                        'title' => 'Temple Maintenance',
                        'description' => 'Preserving architecture, sanctum care, and devotional spaces.',
                        'button' => 'Support Now',
                    ],
                    [
                        'title' => 'Annadanam',
                        'description' => 'Serving meals to devotees, guests, and the wider community.',
                        'button' => 'Support Now',
                    ],
                ],
                'quick_options' => [
                    ['label' => 'Annadanam Contribution', 'amount' => 'Rs 1,000'],
                    ['label' => 'Puja Sponsorship', 'amount' => 'Rs 2,500'],
                    ['label' => 'General Donation', 'amount' => 'Enter amount'],
                ],
                'cta' => ['label' => 'Complete Donation', 'href' => '/donations'],
            ],
        ],
        'about' => [
            'meta' => [
                'title' => 'About | AnkammaThalli Temple',
                'description' => 'Discover the temple history, mission, values, and heritage milestones on the AnkammaThalli Temple About page.',
            ],
            'hero' => [
                'eyebrow' => 'Dedicated to the Divine',
                'title' => 'A Sanctuary of',
                'highlight' => 'Eternal Grace.',
                'image' => 'assets/images/placeholders/about-hero-sanctum.svg',
            ],
            'history' => [
                'eyebrow' => 'Circa 1845',
                'title' => 'Our History',
                'paragraphs' => [
                    'Founded on the principles of devotion, community care, and sacred continuity, the temple has stood for generations as a spiritual landmark for local families and visiting devotees.',
                    'What began as a modest place of worship evolved over time through collective faith, craftsmanship, and service, preserving the atmosphere of reverence that defines the temple today.',
                    'This sample content is temporary, but the structure is already prepared so the final origin story, milestones, and archival media can later be managed from the admin backend.',
                ],
                'quote' => 'The temple is not just a structure of stone, but a living testament to the faith that binds us across generations.',
                'main_image' => 'assets/images/placeholders/about-stone-gateway.svg',
                'secondary_image' => 'assets/images/placeholders/about-inner-lamps.svg',
                'secondary_title' => 'The Legacy Lives On',
                'secondary_text' => 'Today, the temple remains a place for worship, reflection, and community gatherings, carrying forward the same spirit of devotion that shaped its earliest years.',
            ],
            'mission' => [
                'eyebrow' => 'The Path Ahead',
                'title' => 'Our Mission',
                'items' => [
                    [
                        'symbol' => '01',
                        'title' => 'Preservation',
                        'description' => 'To safeguard sacred architecture, rituals, and oral traditions so the temple heritage remains alive for future generations.',
                    ],
                    [
                        'symbol' => '02',
                        'title' => 'Service',
                        'description' => 'To support the community through hospitality, shared meals, educational initiatives, and compassionate outreach.',
                    ],
                    [
                        'symbol' => '03',
                        'title' => 'Enlightenment',
                        'description' => 'To create a space for prayer, reflection, and spiritual learning grounded in devotion and mutual respect.',
                    ],
                ],
            ],
            'values' => [
                'intro_title' => 'Our Values',
                'intro_text' => 'The principles that guide the temple community, the service it offers, and the spiritual atmosphere it protects.',
                'feature_image' => 'assets/images/placeholders/about-lantern-glow.svg',
                'items' => [
                    [
                        'number' => '01',
                        'title' => 'Dharma',
                        'description' => 'The righteous path that shapes our spiritual, cultural, and administrative decisions.',
                    ],
                    [
                        'number' => '02',
                        'title' => 'Seva',
                        'description' => 'Selfless service to devotees, guests, and the wider community with humility and care.',
                    ],
                    [
                        'number' => '03',
                        'title' => 'Shanti',
                        'description' => 'A commitment to peace, reflection, and harmony in both worship and daily life.',
                    ],
                    [
                        'number' => '04',
                        'title' => 'Prakriti',
                        'description' => 'Respect for the natural world as part of the sacred environment that surrounds temple life.',
                    ],
                ],
            ],
            'facts' => [
                ['value' => '175+', 'label' => 'Years of Heritage'],
                ['value' => '50k+', 'label' => 'Monthly Devotees'],
                ['value' => '12', 'label' => 'Annual Festivals'],
                ['value' => '200+', 'label' => 'Community Volunteers'],
            ],
        ],
        'gallery' => [
            'meta' => [
                'title' => 'Gallery | AnkammaThalli Temple',
                'description' => 'Gallery page design is queued for a later public-site milestone.',
            ],
            'eyebrow' => 'Planned Page',
            'title' => 'Gallery will be implemented from the approved design.',
            'description' => 'This route is active now so navigation is wired, but the full gallery layout will be built in a dedicated milestone.',
            'cta' => ['label' => 'Return Home', 'href' => '/'],
        ],
        'events' => [
            'meta' => [
                'title' => 'Events | AnkammaThalli Temple',
                'description' => 'Events page design is queued for a later public-site milestone.',
            ],
            'eyebrow' => 'Planned Page',
            'title' => 'Events page will follow after the shared foundation.',
            'description' => 'The Events design has been mapped from your export and will reuse the same card system and typography established on the Home page.',
            'cta' => ['label' => 'Return Home', 'href' => '/'],
        ],
        'donations' => [
            'meta' => [
                'title' => 'Donations | AnkammaThalli Temple',
                'description' => 'Donations page design is queued for a later public-site milestone.',
            ],
            'eyebrow' => 'Planned Page',
            'title' => 'Donations page is ready for a focused build pass.',
            'description' => 'For now the Home page contains sample donation content while the full donation screen waits for its own milestone.',
            'cta' => ['label' => 'Return Home', 'href' => '/'],
        ],
        'contact' => [
            'meta' => [
                'title' => 'Contact | AnkammaThalli Temple',
                'description' => 'Contact page design is queued for a later public-site milestone.',
            ],
            'eyebrow' => 'Planned Page',
            'title' => 'Contact page is wired and reserved.',
            'description' => 'We will add the final visit, contact form, and map layout from your approved design in a later step.',
            'cta' => ['label' => 'Return Home', 'href' => '/'],
        ],
        'blog' => [
            'meta' => [
                'title' => 'Blog | AnkammaThalli Temple',
                'description' => 'Blog page design is queued for a later public-site milestone.',
            ],
            'eyebrow' => 'Planned Page',
            'title' => 'Blog page will be added with the same editorial styling.',
            'description' => 'The route is already reserved so we can add the exported blog design without restructuring the project later.',
            'cta' => ['label' => 'Return Home', 'href' => '/'],
        ],
        'admin' => [
            'meta' => [
                'title' => 'Admin | AnkammaThalli Temple',
                'description' => 'Admin screens are intentionally deferred until the public site is complete.',
            ],
            'eyebrow' => 'Deferred Area',
            'title' => 'Admin pages are planned after the public site.',
            'description' => 'The admin login and content-management screens from your export are noted, but implementation is intentionally postponed per your request.',
            'cta' => ['label' => 'Return Home', 'href' => '/'],
        ],
    ],
];
