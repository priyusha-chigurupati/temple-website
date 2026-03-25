INSERT INTO locales (code, name, native_name, is_default, is_active, sort_order)
VALUES
    ('en', 'English', 'English', 1, 1, 1),
    ('te', 'Telugu', 'తెలుగు', 0, 1, 2);

INSERT INTO site_settings (setting_key, setting_value, setting_type, is_translatable)
VALUES
    ('site.default_locale', 'en', 'string', 0),
    ('site.supported_locales', 'en,te', 'string', 0),
    ('site.name', NULL, 'string', 1),
    ('site.tagline', NULL, 'string', 1),
    ('site.contact.phone', NULL, 'string', 0),
    ('site.contact.email', NULL, 'string', 0),
    ('site.contact.address', NULL, 'text', 1),
    ('site.contact.map_url', NULL, 'string', 0);
