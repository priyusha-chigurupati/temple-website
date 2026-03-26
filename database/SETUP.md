# Database Setup

This project is being prepared for a PHP + MySQL deployment with future multilingual support.

## Why The Schema Uses Translation Tables

The database is designed so that language-neutral records stay in one table and translated text lives in companion `*_translations` tables.

Examples:

- `pages` + `page_translations`
- `page_sections` + `page_section_translations`
- `events` + `event_translations`
- `blog_posts` + `blog_post_translations`
- `gallery_items` + `gallery_item_translations`

This is the best fit for the current goal:

- build in English first
- add Telugu later
- support additional languages later without changing the core schema

## Files

- `database/schema.sql`: main schema
- `database/seeds.sql`: starting locales and baseline site settings
- `.env.example`: environment variables for DB and locale config

## What You Need To Do Before I Connect The Site To MySQL

For local development, you do not need to do anything yet unless you want to test the DB-backed version immediately.

If you do want to test locally:

1. Create a MySQL or MariaDB database using `utf8mb4`.
2. Copy `.env.example` to `.env`.
3. Fill in the real DB credentials in `.env`.
4. Import `database/schema.sql`.
5. Import `database/seeds.sql`.

## What You Will Need To Do On Hostinger Later

When we are ready to deploy the DB-backed/admin version:

1. Create a new MySQL database and database user in Hostinger hPanel.
2. Save the database host, database name, username, password, and port.
3. Make sure the database uses `utf8mb4`, since English + Telugu content must be stored safely.
4. Import `database/schema.sql` into the new database using phpMyAdmin or Hostinger's DB tools.
5. Import `database/seeds.sql`.
6. Create a production `.env` file with the Hostinger DB credentials.
7. Set:
   - `APP_DEFAULT_LOCALE=en`
   - `APP_SUPPORTED_LOCALES=en,te`
8. Make sure uploaded-media directories are writable by PHP.

## Important Multilingual Note

The current public site still renders English sample content from `data/site.php`.

The schema is now ready for:

- English content rows
- Telugu content rows
- locale switching later

When we implement multilingual display, the public site will read content by locale, for example:

- English -> `en`
- Telugu -> `te`

The same event, blog post, or page section can then have multiple translations without creating duplicate records.

## Current Local Test Command For Events

After the local DB is created and `.env` is filled in, the current sample Events can be inserted into MySQL with:

```powershell
C:\xampp\php\php.exe .\scripts\seed_events.php
```

That command seeds the current English sample Events into:

- `events`
- `event_translations`
- `media`
- `media_translations`

The current sample Blog content can be inserted into MySQL with:

```powershell
C:\xampp\php\php.exe .\scripts\seed_blog.php
```

That command seeds the current English sample Blog content into:

- `blog_posts`
- `blog_post_translations`
- `blog_categories`
- `blog_category_translations`
- `blog_post_categories`
- `media`
- `media_translations`

The current sample Gallery content can be inserted into MySQL with:

```powershell
C:\xampp\php\php.exe .\scripts\seed_gallery.php
```

That command seeds the current English sample Gallery content into:

- `gallery_categories`
- `gallery_category_translations`
- `gallery_items`
- `gallery_item_translations`
- `media`
- `media_translations`

The current sample About page content can be inserted into MySQL with:

```powershell
C:\xampp\php\php.exe .\scripts\seed_about.php
```

That command seeds the current English sample About content into:

- `pages`
- `page_translations`
- `page_sections`
- `page_section_translations`
- `media`
- `media_translations`

The current sample Home page content can be inserted into MySQL with:

```powershell
C:\xampp\php\php.exe .\scripts\seed_home.php
```

That command seeds the current English sample Home content into:

- `pages`
- `page_translations`
- `page_sections`
- `page_section_translations`
- `media`
- `media_translations`
