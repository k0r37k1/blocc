# blocc

A minimal, fast, and opinionated blog platform built with Laravel 12 and Filament 4. Designed for developers who want a clean blog with a powerful admin panel — deployable on shared hosting without Node.js.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2+, Laravel 12 |
| Admin Panel | Filament 4, Livewire 3 |
| Frontend | Blade, Alpine.js, Tailwind CSS 4 |
| Build Tool | Vite 7 |
| Database | SQLite (default) |
| Media | Spatie Media Library |
| Code Style | Laravel Pint, Biome |
| Testing | PHPUnit 11 |

## Features

### Blog
- Rich text editor with syntax highlighting (Phiki) and auto-generated heading anchors
- Featured images with automatic WebP conversion (thumbnail + medium)
- Reading time calculation (200 wpm)
- Auto-generated excerpts
- Categories with color badges and tags
- Draft / Published workflow
- RSS feed (`/feed`) and sitemap (`/sitemap.xml`)
- Archive view

### Admin Panel
- Dashboard with stats overview, recent posts, and draft reminders
- Full CRUD for posts, pages, categories, tags, and media
- Settings page (blog name, description, social links, custom scripts)
- User profile with avatar and social links (GitHub, X, LinkedIn, Instagram, Bluesky)
- Username-based login with forced password change on first login

### i18n
- German and English translations
- Session-based locale switching with language toggle in admin and frontend

### Security
- Content Security Policy headers (production)
- HTML sanitization via HTMLPurifier
- Rate-limited login (5 attempts)

### Deployment
- Build output committed to repo — no Node.js required on server
- SQLite by default — no database server needed
- Database-backed sessions, cache, and queue

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ (development only)

## Installation

```bash
git clone https://github.com/k0r37k1/blocc.git
cd blocc
composer run setup
```

This will install dependencies, copy `.env.example`, generate an app key, run migrations, and build frontend assets.

### Configure Admin User

Edit `.env` to set your admin credentials:

```env
ADMIN_USERNAME=admin
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=password
```

Then seed the database:

```bash
php artisan db:seed
```

### Default Login

After seeding, log in at `/admin` with:

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `password` |

You will be prompted to change your password on first login.

## Development

```bash
composer run dev
```

Starts the Laravel server, queue worker, log viewer (Pail), and Vite dev server concurrently.

## Scripts

| Command | Description |
|---------|-------------|
| `composer run setup` | Initial project setup |
| `composer run dev` | Start all dev services |
| `composer test` | Run PHPUnit test suite |
| `npm run dev` | Vite dev server |
| `npm run build` | Build frontend assets |
| `vendor/bin/pint` | Format PHP code |

## Project Structure

```
app/
  Filament/          # Admin panel (resources, pages, widgets)
  Http/Controllers/  # Public controllers (blog, feed, sitemap)
  Models/            # Eloquent models
  Services/          # Content processing pipeline
resources/
  views/             # Blade templates
  css/               # Tailwind entry point
  js/                # Alpine.js entry point
lang/                # Translation files (de, en)
```

## Notable Packages

- [filament/filament](https://filamentphp.com) — Admin panel framework
- [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) — Media management with image conversions
- [spatie/laravel-backup](https://github.com/spatie/laravel-backup) — Database and file backups
- [stevebauman/purify](https://github.com/stevebauman/purify) — HTML sanitization
- [phiki/phiki](https://github.com/phikiphp/phiki) — Syntax highlighting for code blocks

## License

[MIT](LICENSE)
