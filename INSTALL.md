# Installation

## Voraussetzungen

- PHP 8.2+ mit `pdo_sqlite` Extension
- Kein MySQL, kein Node.js, kein SSH noetig

## Installation

### 1. Vorbereiten (lokal)

```bash
git clone https://github.com/k0r37k1/blocc.git
cd blocc
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
```

### 2. Konfigurieren

`.env` anpassen:

```env
APP_NAME="Dein Blog"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://deine-domain.de
APP_LOCALE=de

ADMIN_USERNAME=dein-username
ADMIN_EMAIL=dein@email.de
ADMIN_PASSWORD=ein-sicheres-passwort
```

### 3. Hochladen

Kompletten Ordner per FTP/SFTP auf den Server laden.

### 4. Document Root

Im Hosting-Panel den Document Root auf den `public/`-Ordner setzen.

Falls das nicht moeglich ist, diese `.htaccess` ins Hauptverzeichnis legen:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 5. Fertig

- Blog: `https://deine-domain.de`
- Admin: `https://deine-domain.de/admin`

Beim ersten Login wirst du aufgefordert, dein Passwort zu aendern.

## Updates

Lokal pullen, Dependencies aktualisieren, migrieren, hochladen:

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

Dann die geaenderten Dateien per FTP ersetzen.

## Troubleshooting

| Problem | Loesung |
|---------|---------|
| 500 Error | `storage/logs/laravel.log` pruefen, Berechtigungen von `storage/` und `bootstrap/cache/` auf 775 setzen |
| Bilder fehlen | Symlink `public/storage` -> `storage/app/public` erstellen (`php artisan storage:link`) oder manuell kopieren |
| SQLite-Fehler | `pdo_sqlite` im Hosting-Panel aktivieren |
