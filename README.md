# blocc

> A minimal, self-hosted blog platform built with Laravel — deployable on shared hosting without Node.js.

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![License](https://img.shields.io/github/license/k0r37k1/blocc)](LICENSE)

---

## Overview

blocc is an opinionated, single-user blog engine for developers who want full control over their content without managing a database server or a Node.js runtime in production.

- **SQLite by default** — no database server required
- **Build output committed** — no Node.js on the server
- **Shared hosting friendly** — runs on any PHP 8.4 host with Composer

---

## Features

**Writing**
- Rich text editor with syntax highlighting ([Phiki](https://github.com/phikiphp/phiki)) and auto-generated heading anchors
- Draft / Published workflow
- Auto-generated excerpts and reading time
- Featured images with automatic WebP conversion
- Categories, tags, and archive view

**Readers**
- Comment system with replies, editing, and Gravatar avatars
- RSS feed at `/feed`
- Sitemap at `/sitemap.xml`

**Admin Panel** *(powered by Filament)*
- Dashboard with post stats, recent drafts, and quick actions
- Full CRUD for posts, pages, categories, tags, and media
- Comment moderation
- Appearance settings — accent color, fonts, syntax theme, favicon, logo
- Site settings — name, tagline, social links, custom `<head>` scripts
- User profile with avatar and social links

**Internationalisation**
- German and English translations included
- Session-based locale switching in admin and frontend

**Security**
- Content Security Policy headers (production)
- HTML sanitization via HTMLPurifier
- Rate-limited login (5 attempts / minute)
- Pre-commit secret scanning via [gitleaks](https://github.com/gitleaks/gitleaks)

---

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Admin Panel | Filament 5, Livewire 4 |
| Frontend | Blade, Alpine.js, Tailwind CSS 4 |
| Database | SQLite |
| Media | Spatie Media Library |
| Testing | PHPUnit 12 |

---

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ *(development only)*

---

## Installation

```bash
git clone https://github.com/k0r37k1/blocc.git
cd blocc
composer run setup
```

`composer run setup` installs dependencies, copies `.env.example`, generates an app key, runs migrations, and builds frontend assets.

### Configure your admin account

Open `.env` and set:

```env
ADMIN_USERNAME=yourname
ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=changeme
```

Then seed the database:

```bash
php artisan db:seed
```

Log in at `/admin` — you will be prompted to change your password on first login.

---

## Development

```bash
composer run dev
```

Starts the Laravel server, queue worker, Pail log viewer, and Vite dev server concurrently.

| Command | Description |
|---|---|
| `composer run setup` | Initial project setup |
| `composer run dev` | Start all dev services |
| `composer test` | Run the test suite |
| `npm run build` | Build frontend assets |
| `vendor/bin/pint` | Format PHP (Laravel Pint) |

---

## Deployment

blocc is designed for shared hosting. The frontend build output is committed to the repository, so no Node.js runtime is needed on the server.

See [INSTALL](INSTALL) for a step-by-step shared hosting guide.

---

## Project Structure

```
app/
  Filament/           Admin panel — resources, pages, widgets
  Http/Controllers/   Public blog controllers
  Livewire/           Livewire components
  Models/             Eloquent models
  Services/           Content processing pipeline
  Enums/              PHP enums
  Notifications/      Mail and database notifications
  Support/            Helper classes
resources/
  views/              Blade templates
  css/                Tailwind entry point
  js/                 Alpine.js entry point
lang/                 Translations (de, en)
```

---

## Contributing

Issues and pull requests are welcome. For larger changes, open an issue first to discuss what you'd like to change.

---

## License

[MIT](LICENSE)
