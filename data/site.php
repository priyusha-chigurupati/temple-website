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
                'description' => 'Explore temple architecture, sacred rituals, devotional moments, and festival imagery in the AnkammaThalli Temple gallery.',
            ],
            'title' => 'Visual Chronicles of Devotion',
            'highlight' => 'Devotion',
            'description' => 'Explore the sacred beauty of AnkammaThalli Temple through a curated collection of architectural marvels, vibrant festivities, and daily spiritual rituals.',
            'filters' => [
                ['label' => 'All Collections', 'active' => true],
                ['label' => 'Festivals', 'active' => false],
                ['label' => 'Architecture', 'active' => false],
                ['label' => 'Daily Rituals', 'active' => false],
                ['label' => 'Pilgrims', 'active' => false],
            ],
            'items' => [
                [
                    'title' => 'The Golden Gopuram',
                    'category' => 'Architecture',
                    'image' => 'assets/images/placeholders/gallery-gopuram.svg',
                    'size' => 'feature-wide',
                ],
                [
                    'title' => 'Brahmotsavam Splendor',
                    'category' => 'Festivals',
                    'image' => 'assets/images/placeholders/gallery-brahmotsavam.svg',
                    'size' => 'feature-tall',
                ],
                [
                    'title' => 'Devi Ankamma Thalli',
                    'category' => 'Deity',
                    'image' => 'assets/images/placeholders/gallery-deity-face.svg',
                    'size' => 'square',
                ],
                [
                    'title' => 'Whispering Stones',
                    'category' => 'Architecture',
                    'image' => 'assets/images/placeholders/gallery-stone-medallion.svg',
                    'size' => 'square',
                ],
                [
                    'title' => 'Navaratri Devotion',
                    'category' => 'Rituals',
                    'image' => 'assets/images/placeholders/gallery-navaratri.svg',
                    'size' => 'square',
                ],
                [
                    'title' => 'Deepotsavam Night',
                    'category' => 'Festivals',
                    'image' => 'assets/images/placeholders/gallery-deepam-wide.svg',
                    'size' => 'wide-banner',
                ],
            ],
            'cta' => [
                'title' => 'Capturing the Divine?',
                'description' => 'We invite devotees to share their photographs of the temple. Your perspective could be featured in our official gallery.',
                'button' => 'Submit Your Photos',
            ],
        ],
        'events' => [
            'meta' => [
                'title' => 'Events | AnkammaThalli Temple',
                'description' => 'View ongoing rituals, upcoming festivals, and temple celebrations on the AnkammaThalli Temple events page.',
            ],
            'eyebrow' => 'Temple Calendar',
            'title' => 'Festivals & Sacred Gatherings',
            'description' => 'Join us in celebrating our rich heritage through spiritual rituals, community feasts, and traditional music.',
            'ongoing' => [
                [
                    'label' => 'Ongoing',
                    'date' => 'Mar 15 - Mar 25',
                    'title' => 'Annual Brahmotsavam Celebrations',
                    'description' => 'Experience the festival atmosphere through daily processions, floral offerings, devotional music, and special puja rituals across the temple grounds.',
                    'cta' => 'View Daily Schedule',
                    'image' => 'assets/images/placeholders/events-ongoing-lamps.svg',
                    'layout' => 'image-left',
                ],
                [
                    'label' => 'Ongoing',
                    'date' => 'Daily 6:00 PM',
                    'title' => 'Sandhya Arathi & Bhajan Mandali',
                    'description' => 'Join the evening congregation for sacred arathi followed by devotional singing in a calm temple atmosphere open to all visitors and devotees.',
                    'cta' => 'Join Online Stream',
                    'image' => 'assets/images/placeholders/events-ongoing-sanctum.svg',
                    'layout' => 'image-right',
                ],
            ],
            'upcoming' => [
                [
                    'day' => '14',
                    'month' => 'April',
                    'title' => 'Vishu & Spring Festival',
                    'description' => 'A celebration of new beginnings with dawn darshan, festive colors, and a traditional community meal.',
                    'cta' => 'Pre-register',
                    'image' => 'assets/images/placeholders/events-upcoming-vishu.svg',
                ],
                [
                    'day' => '01',
                    'month' => 'May',
                    'title' => 'Navaratri Mahotsavam',
                    'description' => 'Nine nights of devotion honoring the divine feminine through music, chanting, and decorated sanctum rituals.',
                    'cta' => 'Event Details',
                    'image' => 'assets/images/placeholders/events-upcoming-navaratri.svg',
                ],
                [
                    'day' => '22',
                    'month' => 'May',
                    'title' => 'Kartika Deepotsavam',
                    'description' => 'Witness the temple illuminated by rows of traditional lamps during this auspicious evening celebration.',
                    'cta' => 'Donate Lamps',
                    'image' => 'assets/images/placeholders/events-upcoming-deepam.svg',
                ],
            ],
            'sponsor_cta' => [
                'title' => 'Sponsor a Sacred Event',
                'description' => 'Contribute to the conduct of divine festivals and receive the blessings of Annadanam and Archana performed in your name.',
                'primary' => 'Sponsor Now',
                'secondary' => 'Volunteer Registration',
            ],
        ],
        'donations' => [
            'meta' => [
                'title' => 'Donations | AnkammaThalli Temple',
                'description' => 'Support temple rituals, annadanam, and preservation work through the AnkammaThalli Temple donations page.',
            ],
            'eyebrow' => 'Support Our Sanctum',
            'title_prefix' => 'Every offering builds a',
            'title_highlight' => 'legacy of faith.',
            'description' => 'Your contributions sustain the daily rituals, the preservation of our sacred architecture, and our mission to serve the community through spiritual guidance and nourishment.',
            'impact' => [
                'title' => 'Impact of Your Gift',
                'description' => 'From maintaining temple spaces to supporting annadanam and festival seva, each donation directly strengthens worship and community care.',
            ],
            'hero_image' => 'assets/images/placeholders/donations-hero-sanctum.svg',
            'transparency' => [
                'stat' => '100%',
                'label' => 'Transparency in utilization of funds',
            ],
            'methods_title' => 'Ways to Contribute',
            'methods' => [
                [
                    'icon' => 'AA',
                    'tone' => 'gold',
                    'title' => 'Annadanam Offering',
                    'description' => 'Sponsor a day\'s meal for devotees. Feeding with compassion remains one of the temple\'s most cherished forms of seva.',
                    'type' => 'price',
                    'meta_label' => 'Sponsorship starts at',
                    'meta_value' => 'Rs. 5,001',
                    'button' => 'Select Offering',
                    'button_style' => 'outline',
                ],
                [
                    'icon' => 'UP',
                    'tone' => 'peach',
                    'title' => 'Instant UPI Transfer',
                    'description' => 'Quick and secure payments via PhonePe, Google Pay, or any supported UPI app using the official temple ID below.',
                    'type' => 'code',
                    'code' => 'ankammathalli.temple@upi',
                    'button' => 'Open App',
                    'button_style' => 'gradient',
                ],
                [
                    'icon' => 'BT',
                    'tone' => 'amber',
                    'title' => 'Direct Bank Transfer',
                    'description' => 'Ideal for international transfers or larger endowments. Please include your name in the note for receipt tracking.',
                    'type' => 'details',
                    'details' => [
                        ['label' => 'Bank', 'value' => 'State Bank of India'],
                        ['label' => 'Account No.', 'value' => '9876543210'],
                        ['label' => 'IFSC', 'value' => 'SBIN0001234'],
                    ],
                ],
            ],
            'instructions_title' => 'Donation Instructions',
            'instructions' => [
                [
                    'number' => '01',
                    'title' => 'Select Your Seva',
                    'description' => 'Choose a contribution path that aligns with your intention, whether it is daily worship, annadanam, or general temple care.',
                ],
                [
                    'number' => '02',
                    'title' => 'Complete Payment',
                    'description' => 'Use one of the methods listed above and keep your transaction reference or screenshot ready for temple records.',
                ],
                [
                    'number' => '03',
                    'title' => 'Notify the Temple',
                    'description' => 'Share your donation details so the team can reconcile the payment and prepare your acknowledgement receipt.',
                ],
            ],
            'benefit' => [
                'title' => 'Tax Benefits',
                'description' => 'Sample content: qualifying donations can later be configured to mention receipt and tax-exemption details once the final legal and accounting copy is confirmed.',
            ],
            'form' => [
                'title' => 'Notify Us of Your Donation',
                'description' => 'Help us track your contribution and send your digital acknowledgement.',
                'fields' => [
                    [
                        'label' => 'Full Name',
                        'name' => 'full_name',
                        'type' => 'text',
                        'placeholder' => 'Aditya Sharma',
                    ],
                    [
                        'label' => 'Amount',
                        'name' => 'amount',
                        'type' => 'text',
                        'placeholder' => '5001',
                    ],
                    [
                        'label' => 'Reference ID / Transaction ID',
                        'name' => 'reference_id',
                        'type' => 'text',
                        'placeholder' => 'TXN98321045',
                    ],
                    [
                        'label' => 'Message / Dedication',
                        'name' => 'message',
                        'type' => 'textarea',
                        'placeholder' => 'In memory of... / Prayers for...',
                    ],
                ],
                'button' => 'Submit Details',
            ],
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
