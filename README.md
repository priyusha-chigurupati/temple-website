# Temple Website

Milestone 1 contains the public-site foundation and the Home page implementation in plain PHP.

## Stack

- PHP 8.2+
- MySQL or MariaDB
- Apache with `mod_rewrite`
- Server-rendered PHP views
- Vanilla CSS and small progressive-enhancement JavaScript later if needed

## Structure

- `public/` web root
- `app/` lightweight application classes
- `views/` templates and partials
- `data/` temporary sample content for development
- `database/` multilingual-ready schema, seeds, and setup notes for the future MySQL-backed CMS
- `reference/` Figma export source files

## Local Run

Point your local web server document root to `public/`.

Example with PHP built-in server:

```bash
php -S localhost:8000 -t public router.php
```

On this machine, you can also run:

```powershell
.\serve-local.ps1
```

Then open `http://localhost:8000` in your browser.

## Deployment Note

For shared hosting, deploy the contents of `public/` as the web root and keep the rest of the project outside the public web directory when possible.

## Database Foundation

The next application phase is MySQL-backed content and admin CRUD.

The database has now been prepared with future multilingual support in mind:

- English first
- Telugu next
- more languages later without restructuring the core tables

See `database/SETUP.md` for:

- local DB setup
- Hostinger DB setup steps
- locale planning
