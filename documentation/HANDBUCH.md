# blocc – Handbuch (codegenau)

Diese Datei beschreibt **nur**, was sich anhand des Repos **verifizieren** lässt: öffentliche Routen, Filament-Oberfläche, Befehle und Datenflüsse. Keine Wunschliste – bei Zweifeln gilt der Quellcode.

**Versionshinweis:** `composer.json`: PHP `^8.3`, Laravel `^12`, Filament `^5`, Livewire `^4`.

---

## 1. Zugriff Admin

- **URL:** `/admin`
- **Panel-Zugang:** `User::canAccessPanel()` erlaubt nur **`users.id === 1`** (`app/Models/User.php`).
- **Erzwungenes Profil:** Ist `must_change_credentials` gesetzt, leitet `EnsureCredentialsChanged` auf die Filament-Profilseite um (alle anderen Admin-Routen blockiert bis Passwort geändert).

---

## 2. Öffentliche Routen (`routes/web.php`)

| Methode & Pfad | Name | Controller / View |
|----------------|------|-------------------|
| `GET /` | `blog.index` | `BlogController@index` |
| `GET /blog/{post}` | `blog.show` | `BlogController@show` (Slug) |
| `GET /kategorie/{category}` | `category.show` | `CategoryController@show` |
| `GET /tag/{tag}` | `tag.show` | `TagController@show` |
| `GET /archiv` | `archive` | `ArchiveController@index` |
| `GET /seite/{page}` | `page.show` | `PageController@show` (Slug) |
| `GET /feed` | `feed` | `FeedController` |
| `GET /sitemap.xml` | `sitemap` | `SitemapController` (liefert Datei; Generierung: `sitemap:generate`) |
| `GET /locale/{locale}` | `locale.switch` | Closure; erlaubte Werte: `SetLocale::SUPPORTED_LOCALES` |
| `GET /newsletter/bestaetigt` | `newsletter.confirmed` | View `newsletter.confirmed` |

**Middleware (global, `bootstrap/app.php`):** Web-Stack + `SetLocale`; danach `ContentSecurityPolicy` (CSP-Header u. a. nur wenn **nicht** `app()->environment('local')`).

**Auf der Post-Detailseite (`resources/views/blog/show.blade.php`), sofern im Code umgesetzt:** verwandte Beiträge, optional TOC (`toc_enabled`), Kommentarblock, Autorenbox, Link vom Autorennamen zu `#post-author`, optional Newsletter-Karte wenn `Setting::get('newsletter_enabled') === '1'`.

---

## 3. Filament: Was im UI erreichbar ist

Ressourcen werden unter `app/Filament/Resources/` per `AdminPanelProvider` eingebunden. **Globale Suche:** `mod+k` (Konfiguration im Panel-Provider).

### 3.1 Gruppe „Content“

| Eintrag | Im Code | Bedienbar |
|---------|---------|-----------|
| **Posts** | `PostResource` | Liste, Erstellen, Bearbeiten, Löschen. Tabelle/Konfiguration: `PostsTable`, Formular: `PostForm`. Navigations-Badge = Anzahl Entwürfe. Globale Suche: Attribute laut `getGloballySearchableAttributes()`. |
| **Pages** | `PageResource` | Wie Posts; Formular `PageForm`. **Sortierung** in der Liste per `sort_order` (`PagesTable`). |
| **Pages mit `is_system`** | `PagesTable` | **Bearbeiten**- und **Löschen**-Aktionen sind **ausgeblendet**; veröffentlicht-Umschalter **deaktiviert**. Text in der Tabelle: Status „Gesperrt“, Slug-Spalte „—“. **Hinweis:** Im geprüften Code gibt es **keine** zusätzliche `authorize`/Abbruch-Logik auf `EditPage` – technisch wäre eine direkte Edit-URL denkbar; fachlich sind diese Seiten nur über Liste nicht bedienbar. Inhalt von Systemseiten kommt in der Praxis aus `PageSeeder` / DB. |
| **Comments** | `CommentResource` | Nur Listen-Seite (`ListComments`); Badge = ausstehende Kommentare. |
| **Media** | `MediaResource` | Nur **Liste**; `canCreate(): false` – **kein** Upload über diese Ressource. Löschen/Bulk über Tabelle möglich (`ListMedia`). |

**Post-Formular (Auszug, `PostForm`):** Titel, Slug, Kategorie, Tags, Auszug, RichEditor-Body, Lesezeit-Anzeige (Berechnung beim Speichern im Model), Featured Image, Alt-Text (Pflicht wenn Bild gesetzt), Platzhalterbild-Checkbox **nur bei Create**, Kommentare pro Post, TOC, Status.

**Post bearbeiten (`EditPost`):** Aktionen Duplizieren, Auf Website ansehen (nur veröffentlicht), Löschen.

**Seite bearbeiten (`EditPage`):** Auf Website ansehen (nur veröffentlicht), Löschen – **kein** Duplizieren wie beim Post.

### 3.2 Gruppe „Taxonomy“

| Eintrag | Im Code | Bedienbar |
|---------|---------|-----------|
| **Categories** | `CategoryResource` | CRUD |
| **Tags** | `TagResource` | CRUD |

### 3.3 Gruppe „General“

| Eintrag | Beschreibung |
|---------|--------------|
| **My Profile** | Navigation-Item → `EditProfile`: Avatar (Spatie `avatar`), Stammdaten, Social-Felder, Passwort-Regeln wie im Formular. |
| **Blog Settings** | `ManageSettings`, Slug `settings`. Felder und Speichern über `Setting::setMany` – exakt die in `ManageSettings::form()` definierten Eingaben (Blogname, Hero, Farben, Schriften, Code-Theme, Posts pro Seite, Kommentare global, Newsletter + Brevo-IDs, Footer, Head-Scripts). |
| **Header „Backup“** | Ruft `Artisan::call('backup:run', ['--only-db' => true])` auf (`spatie/laravel-backup` ist in `composer.json`). Erfolg hängt von `config/backup.php` und Serverumgebung ab. |
| **Header „Reset Data“** | Ruft `migrate:fresh --seed --force` auf. **Technisch:** komplette Datenbank wird verworfen und neu aufgebaut; anschließend laufen alle Seeder aus `DatabaseSeeder` (u. a. `AdminUserSeeder`, `SettingSeeder`, `PageSeeder`, …). Das ist **kein** „alles behalten außer Inhalt“ – angepasste DB-Inhalte und manuelle Settings sind danach nur wieder da, was die Seeder wiederherstellen. Der Dialogtext im UI kann davon abweichen. |

### 3.4 Dashboard

`Dashboard`: Header-Aktionen **Neuer Post**, **Neue Seite**. Widgets: `BlogStatsOverview`, `RecentPostsWidget`, `DraftReminderWidget` (`app/Filament/Widgets/`).

---

## 4. Livewire (öffentlich, eingebunden in Views)

| Klasse | Verwendung (aus Views ersichtlich) |
|--------|-------------------------------------|
| `PostList` | Startseite, Pagination |
| `ArchiveList` | Archiv |
| `Comments` | Post-Detail unter Bedingung „Kommentare aktiv“ |
| `NewsletterSubscribe` | Footer-Komponente; zusätzlich Karten-Variante wenn Newsletter aktiv (`footer`, Post-Show, `post-list`) |

---

## 5. Benachrichtigungen

- **Neuer Gastkommentar:** `Comments::notifyAdmin()` → `User::query()->first()` erhält `NewCommentNotification` (queued). Es ist **nicht** hart `id === 1` codiert; in einer frischen DB entspricht das typischerweise dem ersten angelegten User (Seeder).

---

## 6. Inhaltsspeicherung Post/Page

- Beim Speichern: `body_raw` = Roh-HTML aus dem Formular; `body` = `PostContentProcessor::process($body)` (Purify-Konfiguration `filament_rich_content`, Phiki für Codeblöcke, Anker für `h2`/`h3`).
- Editor-Befüllung: `afterStateHydrated` nutzt `body_raw ?? body` (Post/Page-Formulare).
- **Toolbar erweitern:** `config/purify.php` und `app/Purify/*` an TipTap-Ausgabe anpassen (Kommentar auch in `PostForm`/`PageForm`).

---

## 7. Kommentare & Zeitplan

- Retention: `config/comments.php` / `.env` `COMMENT_IP_RETENTION_DAYS`.
- Befehl: `comments:anonymize-ips`.
- Schedule: `routes/console.php` → täglich 03:15.

---

## 8. Newsletter / Brevo

- Schalter und IDs nur über **Blog Settings** in der DB (`Setting`).
- API/Keys: Konfiguration im Projekt (z. B. `config/brevo.php`); genaue Doppel-Opt-in-Vorgaben stehen in den Projektregeln / Brevo-Doku.

---

## 9. Artisan (im Repo vorhanden, Auswahl)

| Signatur | Klasse |
|----------|--------|
| `comments:anonymize-ips` | `AnonymizeCommentIps` |
| `sitemap:generate` | `GenerateSitemap` → `public/sitemap.xml` |
| `brevo:test` | `TestBrevoApi` |

`backup:run` wird nicht als eigener Abschnitt dupliziert – siehe Filament-Einstellungsseite.

---

## 10. Was dieses Handbuch absichtlich weglässt

- Keine Garantie, dass **jede** Filament-Zwischenversion alle Labels gleich benennt.
- Keine Aufzählung **aller** Tabellen-Spalten oder **aller** Übersetzungsstrings.
- **Keine** rechtliche Beratung; Datenschutztext liegt in der DB/Seeder, nicht in dieser Datei.

---

*Letzte inhaltliche Prüfung: gegen die genannten Pfade im Repository; bei Refactorings bitte Handbuch mitziehen.*
