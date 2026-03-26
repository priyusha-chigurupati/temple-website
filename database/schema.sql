SET NAMES utf8mb4;

CREATE TABLE locales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    native_name VARCHAR(80) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE password_reset_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_reset_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_reset_tokens_user (user_id),
    INDEX idx_password_reset_tokens_lookup (token_hash, used_at, expires_at)
);

CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NULL,
    mime_type VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE media_translations (
    media_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NULL,
    alt_text VARCHAR(255) NULL,
    PRIMARY KEY (media_id, locale_id),
    CONSTRAINT fk_media_translations_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    CONSTRAINT fk_media_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    template_key VARCHAR(120) NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE page_translations (
    page_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    meta_title VARCHAR(190) NULL,
    meta_description VARCHAR(255) NULL,
    PRIMARY KEY (page_id, locale_id),
    CONSTRAINT fk_page_translations_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    CONSTRAINT fk_page_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE page_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT UNSIGNED NOT NULL,
    section_key VARCHAR(120) NOT NULL,
    section_type VARCHAR(120) NOT NULL DEFAULT 'content',
    settings_json JSON NULL,
    image_id BIGINT UNSIGNED NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_page_section (page_id, section_key),
    CONSTRAINT fk_page_sections_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    CONSTRAINT fk_page_sections_media FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
);

CREATE TABLE page_section_translations (
    section_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    eyebrow VARCHAR(190) NULL,
    heading VARCHAR(190) NULL,
    subheading VARCHAR(190) NULL,
    body_long LONGTEXT NULL,
    body_json JSON NULL,
    PRIMARY KEY (section_id, locale_id),
    CONSTRAINT fk_page_section_translations_section FOREIGN KEY (section_id) REFERENCES page_sections(id) ON DELETE CASCADE,
    CONSTRAINT fk_page_section_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE site_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(190) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type VARCHAR(80) NOT NULL DEFAULT 'string',
    is_translatable TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE site_setting_translations (
    setting_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    setting_value TEXT NULL,
    PRIMARY KEY (setting_id, locale_id),
    CONSTRAINT fk_site_setting_translations_setting FOREIGN KEY (setting_id) REFERENCES site_settings(id) ON DELETE CASCADE,
    CONSTRAINT fk_site_setting_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(190) NOT NULL UNIQUE,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NULL,
    image_id BIGINT UNSIGNED NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    is_featured_home TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_media FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
);

CREATE TABLE event_translations (
    event_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    summary TEXT NOT NULL,
    body_long LONGTEXT NULL,
    schedule_label VARCHAR(190) NULL,
    PRIMARY KEY (event_id, locale_id),
    CONSTRAINT fk_event_translations_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE gallery_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE gallery_category_translations (
    category_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    PRIMARY KEY (category_id, locale_id),
    CONSTRAINT fk_gallery_category_translations_category FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_gallery_category_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE gallery_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    source_type ENUM('admin', 'approved_submission') NOT NULL DEFAULT 'admin',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_featured_home TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_gallery_items_media FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE CASCADE,
    CONSTRAINT fk_gallery_items_category FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE RESTRICT
);

CREATE TABLE gallery_item_translations (
    gallery_item_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    caption TEXT NULL,
    PRIMARY KEY (gallery_item_id, locale_id),
    CONSTRAINT fk_gallery_item_translations_item FOREIGN KEY (gallery_item_id) REFERENCES gallery_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_gallery_item_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE gallery_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submitter_name VARCHAR(120) NOT NULL,
    submitter_email VARCHAR(190) NOT NULL,
    description TEXT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    review_notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_gallery_submissions_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE gallery_submission_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gallery_submission_files_submission FOREIGN KEY (submission_id) REFERENCES gallery_submissions(id) ON DELETE CASCADE
);

CREATE TABLE blog_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE blog_category_translations (
    category_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    PRIMARY KEY (category_id, locale_id),
    CONSTRAINT fk_blog_category_translations_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_blog_category_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE blog_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(190) NOT NULL UNIQUE,
    featured_image_id BIGINT UNSIGNED NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_blog_posts_media FOREIGN KEY (featured_image_id) REFERENCES media(id) ON DELETE SET NULL
);

CREATE TABLE blog_post_translations (
    post_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    excerpt TEXT NOT NULL,
    body_long LONGTEXT NOT NULL,
    meta_title VARCHAR(190) NULL,
    meta_description VARCHAR(255) NULL,
    read_time_label VARCHAR(80) NULL,
    PRIMARY KEY (post_id, locale_id),
    CONSTRAINT fk_blog_post_translations_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_blog_post_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE blog_post_categories (
    post_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, category_id),
    CONSTRAINT fk_blog_post_categories_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_blog_post_categories_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
);

CREATE TABLE donation_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    option_key VARCHAR(120) NOT NULL UNIQUE,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    settings_json JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE donation_option_translations (
    donation_option_id BIGINT UNSIGNED NOT NULL,
    locale_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT NULL,
    amount_label VARCHAR(120) NULL,
    content_json JSON NULL,
    PRIMARY KEY (donation_option_id, locale_id),
    CONSTRAINT fk_donation_option_translations_option FOREIGN KEY (donation_option_id) REFERENCES donation_options(id) ON DELETE CASCADE,
    CONSTRAINT fk_donation_option_translations_locale FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE CASCADE
);

CREATE TABLE donation_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    payment_method VARCHAR(120) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reference_id VARCHAR(190) NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(190) NULL,
    address VARCHAR(255) NOT NULL,
    message TEXT NULL,
    purpose VARCHAR(190) NULL,
    status ENUM('pending', 'reviewed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE contact_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'reviewed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE newsletter_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    source VARCHAR(120) NOT NULL,
    status ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
