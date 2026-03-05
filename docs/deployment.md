# Kopfsalat Deployment Guide

This guide covers deploying Kopfsalat to shared hosting without Node.js or Redis.
Vite build output and Filament assets are committed to the repository, so no build step is needed on the server.

---

## Environment Configuration

Copy the template below to `.env` on your server and fill in the actual values.

```env
APP_NAME=Kopfsalat
APP_ENV=production
APP_KEY=  # Generate with: php artisan key:generate
APP_DEBUG=false
APP_URL=https://kopfsalat.blog

APP_DESCRIPTION="Thoughts served fresh"
APP_LOCALE=de
APP_FALLBACK_LOCALE=de

BCRYPT_ROUNDS=12

LOG_CHANNEL=single
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
DB_JOURNAL_MODE=wal
DB_BUSY_TIMEOUT=5000
DB_SYNCHRONOUS=normal

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=kopfsalat.blog
SESSION_COOKIE=kopfsalat_session
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

MAIL_MAILER=log

ADMIN_NAME=  # Set your admin name
ADMIN_EMAIL=  # Set your admin email
ADMIN_PASSWORD=  # Set a secure password

VITE_APP_NAME="${APP_NAME}"
```

**Required values to fill in:**

| Variable         | Description                                       |
|------------------|---------------------------------------------------|
| `APP_KEY`        | Generate on server with `php artisan key:generate` |
| `DB_DATABASE`    | Absolute path to SQLite file on server             |
| `SESSION_DOMAIN` | Your production domain (e.g., `kopfsalat.blog`)    |
| `ADMIN_NAME`     | Admin user display name                            |
| `ADMIN_EMAIL`    | Admin login email                                  |
| `ADMIN_PASSWORD` | Admin login password (use a strong password)        |

---

## Pre-deployment (Local Machine)

These steps are already done if you're using the committed build output:

- [ ] Run `npm run build` to generate Vite assets in `public/build/`
- [ ] Run `php artisan filament:assets` to publish Filament panel assets
- [ ] Verify `public/build/manifest.json` exists
- [ ] Commit all changes and push to repository

---

## Server Setup (First Time Only)

### 1. Upload Project Files

```bash
# Option A: Git clone
git clone <repository-url> /path/to/kopfsalat
cd /path/to/kopfsalat

# Option B: Upload via FTP/SFTP
# Upload all project files to your hosting directory
```

### 2. Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configure Environment

```bash
# Copy the template (or create .env manually using the template above)
cp .env.production .env

# Edit .env and fill in actual values
nano .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Create Database

```bash
# Create the SQLite database file at the path specified in DB_DATABASE
touch /path/to/database/database.sqlite

# Run migrations
php artisan migrate --force
```

### 6. Seed Admin User

```bash
php artisan db:seed --force
```

This creates the admin user using the `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` values from `.env`.

### 7. Create Storage Symlink

```bash
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public` so uploaded media files are accessible.

### 8. Optimize for Production

```bash
# Cache config, routes, views, and events
php artisan optimize

# Cache Filament components and icons
php artisan filament:optimize
```

### 9. Set File Permissions

Ensure the web server can write to these directories:

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### 10. Configure Web Server Document Root

Point your domain's document root to the `public/` directory:

```
Document root: /path/to/kopfsalat/public
```

---

## Deployment (Subsequent Updates)

```bash
# 1. Pull latest changes
git pull origin main

# 2. Install updated dependencies (if composer.lock changed)
composer install --no-dev --optimize-autoloader

# 3. Run new migrations (if any)
php artisan migrate --force

# 4. Refresh caches
php artisan optimize

# 5. Refresh Filament caches
php artisan filament:optimize
```

No `npm run build` needed on the server -- build output is committed to the repository.

---

## Post-deployment Verification

After each deployment, verify these items:

- [ ] Homepage loads with correct styling (CSS/JS from committed build)
- [ ] Admin login works at `/admin`
- [ ] Media images display correctly (`storage:link` working)
- [ ] Code blocks have syntax highlighting (Phiki rendering)
- [ ] Dark mode toggle works on public pages
- [ ] CSP headers present (check browser DevTools > Network > Response Headers)
- [ ] No external requests in Network tab (DSGVO compliance)
- [ ] `robots.txt` accessible at `/robots.txt` and references correct sitemap URL
- [ ] Sitemap generates correctly at `/sitemap.xml`
- [ ] RSS feed renders correctly at `/feed`

---

## Manual Tasks / Reminders

### OG Default Image

Create a branded image for social sharing:

- **Path:** `public/images/og-default.png`
- **Dimensions:** 1200x630px recommended
- **Purpose:** Used as fallback Open Graph image when posts don't have a featured image
- **Note:** The directory exists but the image needs to be created before launch

### DNS Configuration

- Ensure `kopfsalat.blog` DNS A record points to your hosting server's IP address
- If using `www`, set up a CNAME or redirect as preferred

### SSL / HTTPS

- Verify HTTPS is active on your domain
- Most shared hosts provide free Let's Encrypt certificates via their control panel
- Ensure `APP_URL` in `.env` uses `https://`

### .htaccess (If Needed)

Laravel ships with a `public/.htaccess` for Apache. If HTTPS redirect is not handled by your hosting panel, add these rules to `public/.htaccess`:

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### robots.txt

Verify the sitemap URL in `public/robots.txt` uses the production domain:

```
Sitemap: https://kopfsalat.blog/sitemap.xml
```

---

## Troubleshooting

### Styles Not Loading

- Verify `public/build/manifest.json` exists
- Check that document root points to `public/` directory
- Run `php artisan optimize:clear` and then `php artisan optimize`

### 500 Server Error

- Check `storage/logs/laravel.log` for error details
- Verify `.env` file exists and `APP_KEY` is set
- Ensure `storage/` and `bootstrap/cache/` are writable

### Admin Panel Not Loading

- Verify Filament assets exist in `public/js/filament/` and `public/css/filament/`
- Run `php artisan filament:optimize` to rebuild Filament cache

### Media Images Not Showing

- Run `php artisan storage:link` to create the symlink
- Verify the symlink exists: `ls -la public/storage`

### Database Errors

- Verify the SQLite file path in `DB_DATABASE` is an absolute path
- Ensure the database file and its parent directory are writable
- Check WAL mode compatibility: some shared hosts may need `DB_JOURNAL_MODE=delete`
