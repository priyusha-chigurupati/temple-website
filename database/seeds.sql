INSERT INTO pages (slug, title, meta_title, meta_description, status) VALUES
('home', 'Home', 'Home | AnkammaThalli Temple', 'Welcome to AnkammaThalli Temple.', 'published'),
('about', 'About', 'About | AnkammaThalli Temple', 'Temple story and heritage.', 'draft'),
('gallery', 'Gallery', 'Gallery | AnkammaThalli Temple', 'Temple imagery and moments.', 'draft'),
('events', 'Events', 'Events | AnkammaThalli Temple', 'Festivals and sacred gatherings.', 'draft'),
('donations', 'Donations', 'Donations | AnkammaThalli Temple', 'Ways to support the temple.', 'draft'),
('contact', 'Contact', 'Contact | AnkammaThalli Temple', 'Visit and contact the temple.', 'draft'),
('blog', 'Blog', 'Blog | AnkammaThalli Temple', 'Stories and reflections.', 'draft');

INSERT INTO donation_options (title, description, amount_label, sort_order, is_active) VALUES
('Temple Maintenance', 'Preserving architecture and sacred spaces.', 'Rs 1,000', 1, 1),
('Annadanam', 'Community meal sponsorship for devotees and guests.', 'Rs 2,500', 2, 1),
('General Donation', 'Flexible contribution amount.', NULL, 3, 1);
