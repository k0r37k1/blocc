# Blog Platform – Spezifikation

**Projekt:** Neues eigenständiges Blog-Projekt
**Tech-Stack:** TALL Stack (Tailwind CSS + Alpine.js + Laravel 12 + Livewire 3) + Filament 3
**Erstellt:** 28. Februar 2026
**Status:** Draft v1.4

---

## 1. Projektübersicht

### Vision

Ein minimalistischer, content-fokussierter Blog mit klassischen Blog-Features und Dev-Blog/Changelog-Charakter. Privacy-bewusst, schnell und schlank – serverseitig gerendert mit Blade, interaktive Elemente via Livewire und Alpine.js. Admin-Panel komplett über Filament.

### Kernprinzipien

- **Content First** – Der Inhalt steht im Mittelpunkt, kein visueller Ballast
- **WYSIWYG-Editor** – Rich-Text-Editing via Filament RichEditor (TipTap), Markdown-Import/Export beibehalten
- **Server-Rendered** – Blade-Templates = SEO out-of-the-box, kein SSR-Setup nötig
- **Performance** – SQLite als Datenbank, minimale Dependencies, kein JS-Framework-Overhead
- **SEO-optimiert** – Natives Server-Rendering, Structured Data, saubere URLs
- **Privacy-First** – DSGVO-konform, keine externen Tracker
- **Filament Admin** – Komplettes Admin-Panel mit minimalem Custom-Code

---

## 2. Tech-Stack

### Backend

| Komponente | Technologie |
|---|---|
| Framework | Laravel 12 |
| Datenbank | SQLite |
| Admin Panel | Filament 3 |
| Interaktivität | Livewire 3 |
| Markdown Parser | league/commonmark (mit GFM Extensions) |
| Auth | Filament Shield / Laravel Built-in (Single-User) |

### Frontend

| Komponente | Technologie |
|---|---|
| Templates | Blade + Blade Components |
| Styling | Tailwind CSS 4 |
| JS-Interaktivität | Alpine.js (via Livewire gebundelt) |
| Build Tool | Vite |
| Code Highlighting | Shiki (serverseitig, zero JS) |
| Icons | Blade Heroicons / Blade Icons |
| Dark Mode | Tailwind `dark:` Variant + Alpine.js Toggle |

### Filament 3 – Warum?

Filament ist ein TALL-Stack Admin-Panel-Framework für Laravel. Es ersetzt das komplette custom Admin-Panel:

- **Komplettes Admin-UI** – Dashboard, CRUD-Resources, Forms, Tables, Notifications out-of-the-box
- **Rich-Text-Editor** – TipTap-basierter RichEditor als Formular-Feld
- **Table Builder** – Sortierung, Filterung, Suche, Bulk-Actions, Pagination nativ
- **Form Builder** – Alle Feld-Typen (Text, Select, DatePicker, FileUpload, Toggle, etc.)
- **Livewire-basiert** – Reaktive UI ohne eigenes JS
- **Theming** – Eigenes Theme-System mit Tailwind, Dark Mode built-in
- **Plugin-Ökosystem** – Spatie Media Library, TipTap Editor, Shield (Permissions), etc.
- **Zero Custom JS** – Keine React/Vue/Inertia Dependencies

### TALL Stack – Warum?

| Komponente | Rolle |
|---|---|
| **Tailwind CSS** | Utility-first Styling, konsistent, purgeable |
| **Alpine.js** | Leichtgewichtiges JS für Interaktionen (Dropdowns, Modals, Dark Mode Toggle) |
| **Laravel** | Backend-Framework, Routing, Auth, Eloquent ORM |
| **Livewire** | Server-Side Rendering mit reaktiven Komponenten (ohne API, ohne JS-Framework) |

**Vorteile gegenüber React/Inertia:**

- **Kein Node.js auf Server nötig** – Blade rendert HTML direkt, kein SSR-Setup
- **SEO nativ** – Server-Rendered HTML, Crawler sehen sofort den Content
- **Weniger Complexity** – Kein JS-Build für Admin nötig (Filament bringt alles mit)
- **Kleinerer JS-Footprint** – Alpine.js (~15KB) statt React (~140KB) + Inertia + Lexical
- **Shared Hosting kompatibel** – Läuft überall wo PHP läuft
- **Ein Tech-Stack** – PHP/Blade überall, keine PHP↔JS Context-Switches

---

## 3. Datenmodell

### Posts

| Feld | Typ | Beschreibung |
|---|---|---|
| `id` | integer (PK) | Auto-Increment |
| `title` | string(255) | Titel des Beitrags |
| `slug` | string(255) | URL-Slug (unique) |
| `excerpt` | text (nullable) | Kurzbeschreibung / Teaser |
| `body` | text | HTML-Content (vom RichEditor direkt gespeichert) |
| `body_markdown` | text (nullable) | Markdown-Export (optional, für RSS/Portabilität) |
| `type` | enum | `post`, `changelog`, `page` |
| `status` | enum | `draft`, `published` |
| `featured_image` | string (nullable) | Pfad zum Beitragsbild |
| `meta_title` | string (nullable) | SEO-Titel (Fallback: title) |
| `meta_description` | string (nullable) | SEO-Beschreibung (Fallback: excerpt) |
| `meta` | json (nullable) | Flexible Zusatzfelder (z.B. Changelog-Version, Type) |
| `reading_time` | integer (nullable) | Geschätzte Lesezeit in Minuten |
| `published_at` | datetime (nullable) | Veröffentlichungsdatum |
| `created_at` | datetime | Erstelldatum |
| `updated_at` | datetime | Letzte Änderung |

**Änderung zu v1.3:** `body` speichert direkt HTML (statt Lexical JSON). Kein `body_html` Cache-Feld mehr nötig – der RichEditor gibt HTML aus, das direkt gerendert wird.

### Categories

| Feld | Typ | Beschreibung |
|---|---|---|
| `id` | integer (PK) | Auto-Increment |
| `name` | string(100) | Kategoriename |
| `slug` | string(100) | URL-Slug (unique) |
| `description` | text (nullable) | Beschreibung |
| `sort_order` | integer | Sortierung (default: 0) |

### Tags

| Feld | Typ | Beschreibung |
|---|---|---|
| `id` | integer (PK) | Auto-Increment |
| `name` | string(100) | Tag-Name |
| `slug` | string(100) | URL-Slug (unique) |

### Media

| Feld | Typ | Beschreibung |
|---|---|---|
| `id` | integer (PK) | Auto-Increment |
| `filename` | string(255) | Original-Dateiname |
| `path` | string(255) | Speicherpfad |
| `mime_type` | string(100) | MIME-Typ |
| `size` | integer | Dateigröße in Bytes |
| `alt_text` | string (nullable) | Alt-Text für Barrierefreiheit |
| `created_at` | datetime | Upload-Datum |

### Pivot-Tabellen

- `category_post` – Post ↔ Category (many-to-many)
- `post_tag` – Post ↔ Tag (many-to-many)

---

## 4. Post-Typen

### 4.1 Blog Post (`post`)

Klassischer Blog-Beitrag mit Titel, Beitragsbild, Kategorien und Tags. Längerer Content, SEO-optimiert.

### 4.2 Changelog (`changelog`)

Dev-Blog-Einträge im Changelog-Stil. Kompakt, datumszentriert, optional mit Versions-Tag. Ideal für Projekt-Updates, Release Notes, TIL-Einträge (Today I Learned).

**Zusatzfelder via `meta` JSON:**

```json
{
  "version": "v1.2.0",
  "changelog_type": "added|changed|fixed|removed|security"
}
```

### 4.3 Page (`page`)

Statische Seiten (About, Impressum, Datenschutz). Nicht im Blog-Feed, nicht kategorisiert.

---

## 5. Architektur

### Grundprinzip: Blade + Livewire

```
Browser Request
    → Laravel Router
        → Controller (bereitet Daten auf)
            → return view('blog.show', ['post' => $post])
                → Blade-Template rendert HTML
                    → Browser erhält fertiges HTML
```

Kein SPA, kein Client-Side-Routing – klassisches Server-Rendering. Interaktivität wo nötig über Livewire-Komponenten und Alpine.js.

### Datenfluss-Beispiel: Blog-Post laden

```php
// app/Http/Controllers/BlogController.php
class BlogController extends Controller
{
    public function show(Post $post)
    {
        abort_unless($post->isPublished(), 404);

        return view('blog.show', [
            'post' => $post->load(['categories', 'tags']),
            'related' => $post->relatedPosts(3),
        ]);
    }
}
```

```blade
{{-- resources/views/blog/show.blade.php --}}
<x-blog-layout>
    @section('meta')
        <title>{{ $post->meta_title ?? $post->title }}</title>
        <meta name="description" content="{{ $post->meta_description ?? $post->excerpt }}">
        <meta property="og:title" content="{{ $post->title }}">
        <meta property="og:description" content="{{ $post->excerpt }}">
        <meta property="og:image" content="{{ $post->featured_image }}">
        <meta property="og:type" content="article">
        <link rel="canonical" href="{{ url('/blog/' . $post->slug) }}">
    @endsection

    <article class="prose dark:prose-invert max-w-none">
        <h1>{{ $post->title }}</h1>
        <x-post-meta :post="$post" />
        {!! $post->body !!}
    </article>

    <x-related-posts :posts="$related" />
</x-blog-layout>
```

### Admin: Filament Resource

Das gesamte Admin-Panel wird über Filament Resources abgebildet – kein eigener Controller-Code für CRUD nötig.

```php
// app/Filament/Resources/PostResource.php
class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) =>
                    $set('slug', Str::slug($state))
                ),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\RichEditor::make('body')
                ->required()
                ->columnSpanFull()
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('uploads')
                ->toolbarButtons([
                    'blockquote', 'bold', 'bulletList', 'codeBlock',
                    'h2', 'h3', 'h4', 'italic', 'link', 'orderedList',
                    'redo', 'strike', 'underline', 'undo',
                    'attachFiles',
                ]),

            Forms\Components\Select::make('type')
                ->options([
                    'post' => 'Blog Post',
                    'changelog' => 'Changelog',
                    'page' => 'Seite',
                ])
                ->default('post')
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Entwurf',
                    'published' => 'Veröffentlicht',
                ])
                ->default('draft')
                ->required(),

            Forms\Components\Select::make('categories')
                ->relationship('categories', 'name')
                ->multiple()
                ->preload()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('slug')->required(),
                ]),

            Forms\Components\Select::make('tags')
                ->relationship('tags', 'name')
                ->multiple()
                ->preload()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('slug')->required(),
                ]),

            Forms\Components\FileUpload::make('featured_image')
                ->image()
                ->directory('featured-images'),

            Forms\Components\DateTimePicker::make('published_at')
                ->label('Veröffentlichungsdatum'),

            Forms\Components\Textarea::make('excerpt')
                ->rows(3),

            Forms\Components\Section::make('SEO')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('meta_title'),
                    Forms\Components\Textarea::make('meta_description')->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),
                Tables\Columns\BadgeColumn::make('type'),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Entwurf',
                        'published' => 'Veröffentlicht',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'post' => 'Blog Post',
                        'changelog' => 'Changelog',
                        'page' => 'Seite',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

---

## 6. Features

### 6.1 WYSIWYG-Editor (Filament RichEditor)

**Warum Filament RichEditor?**

Filament's eingebauter RichEditor basiert auf TipTap (ProseMirror) und ist direkt in das Formular-System integriert – kein separates JS-Framework, kein Custom-Setup.

- **Integriert:** Teil von Filament Forms, kein Extra-Package nötig
- **Rich-Text-Features:** Headings, Bold/Italic/Underline/Strikethrough, Links, Listen (ordered/unordered), Blockquotes, Code-Blöcke
- **Bild-Upload:** Drag & Drop direkt im Editor via `->fileAttachmentsDisk()`
- **Toolbar konfigurierbar:** `->toolbarButtons([...])` – nur die Buttons die man braucht
- **HTML-Output:** Speichert direkt sauberes HTML – keine JSON-Zwischenschicht
- **Auto-Save:** Über Filament's Livewire-Integration möglich

**Alternative: filament-tiptap-editor (awcodes)**

Falls der Standard-RichEditor nicht reicht, bietet `awcodes/filament-tiptap-editor` erweiterte Features:

- Drag & Drop Blöcke (Block-Editor-Stil)
- Grid-Layouts im Editor
- Media-Embeds (YouTube, Vimeo)
- Merge Tags
- Custom Blocks
- Source-Code-Editing

**Speicher-Strategie (vereinfacht):**

| Feld | Inhalt | Beschreibung |
|---|---|---|
| `body` | HTML | Primäre Datenquelle, direkt vom RichEditor |
| `body_markdown` | Markdown-Export | Optional, für Portabilität/RSS-Feeds (via league/commonmark HTML→MD) |

**Serverseitige Verarbeitung (Laravel):**

- **Code-Highlighting:** Shiki wird auf HTML-Code-Blöcke angewendet (Post-Processing beim Speichern oder beim Rendern)
- **Markdown-Export:** HTML → Markdown Konvertierung via league/commonmark für RSS-Feeds
- **Sanitization:** HTML-Output wird via `htmlpurifier` oder Laravel's built-in Sanitization bereinigt

### 6.2 SEO-Optimierung

- **Blade = Server-Rendered** – HTML ist sofort komplett, kein SSR-Setup nötig
- **Blade `@section('meta')`** – Dynamische Meta-Tags pro Seite via Layout-Sections
- **Meta-Tags:** `<title>`, `<meta description>`, Open Graph, Twitter Cards
- **Structured Data:** JSON-LD für `BlogPosting` und `Article` (Blade-Partial)
- **Canonical URLs:** Automatisch generiert
- **Sitemap:** Auto-generierte `sitemap.xml` (via `spatie/laravel-sitemap` oder custom)
- **Robots.txt:** Konfigurierbar
- **Saubere URLs:** `/blog/mein-erster-post`, `/changelog`, `/kategorie/laravel`
- **Breadcrumbs:** Structured Data + visuelle Breadcrumbs als Blade-Component

### 6.3 RSS-Feed

- **Haupt-Feed:** `/feed` – Alle veröffentlichten Posts
- **Kategorie-Feeds:** `/kategorie/{slug}/feed`
- **Changelog-Feed:** `/changelog/feed`
- **Format:** RSS 2.0 und Atom (via `spatie/laravel-feed` oder custom Blade-Templates)
- **Content:** Volltext oder Excerpt (konfigurierbar im Admin)
- **Auto-Discovery:** `<link rel="alternate">` im Blade-Layout
- **Wichtig:** RSS-Routen liefern XML direkt

### 6.4 Admin-Panel (Filament 3)

#### Authentifizierung

- Filament Built-in Auth (Login-Page, Guard-Konfiguration)
- Single-User Login
- Remember Me + Session-basiert

#### Dashboard

- Filament Widgets: Post-Count, Drafts, letzte Veröffentlichungen
- Quick-Actions über Custom Widgets
- Alles deklarativ in PHP – kein JS nötig

```php
// app/Filament/Widgets/StatsOverview.php
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Veröffentlicht', Post::published()->count()),
            Stat::make('Entwürfe', Post::draft()->count()),
            Stat::make('Changelog', Post::changelog()->count()),
        ];
    }
}
```

#### Post-Editor (Filament Form)

- **WYSIWYG-Editor:** Filament RichEditor mit konfigurierbarer Toolbar
- **Bild-Upload:** Drag & Drop direkt im Editor + separater FileUpload für Featured Image
- **Auto-Save:** Livewire-basiert (kein TanStack Query nötig)
- **Slug-Generator:** Automatisch aus Titel via `->live(onBlur: true)` + `afterStateUpdated`
- **SEO-Panel:** Filament Section (collapsed) mit Meta-Title, Meta-Description
- **Kategorie-/Tag-Picker:** Filament Select mit `->relationship()` + `->createOptionForm()`
- **Publish-Flow:** Status-Select (Draft/Published) + DateTimePicker für Scheduled Publishing

#### Post-Liste (Filament Table)

- Sortierbar nach Titel, Datum, Status, Typ (Filament Table Columns)
- Filterbar nach Status, Typ (Filament Table Filters)
- Suche über Titel (`->searchable()`)
- Bulk-Actions: Löschen, Status ändern (Filament Bulk Actions)
- Pagination (Filament-Standard, serverseitig)

#### Media-Manager

- Filament FileUpload-Feld mit Drag & Drop
- Oder: `filament/spatie-laravel-media-library-plugin` für erweiterte Media-Verwaltung
- Bildvorschau im Filament-Grid
- Alt-Text-Editing inline

#### Einstellungen

- Filament Settings-Page (via `filament/spatie-laravel-settings-plugin` oder Custom Page)
- Blog-Name, Beschreibung, Tagline
- Social Links
- Feed-Optionen (Volltext/Excerpt)
- SEO-Defaults

### 6.5 Draft/Published Workflow

- **Status:** `draft` → `published`
- **Scheduled Publishing:** `published_at` in der Zukunft → automatische Veröffentlichung (Laravel Scheduler: `php artisan schedule:run`)
- **Preview:** Draft-Posts über signed Preview-URL aufrufbar (`/preview/{post}?signature=...`)
- **Quick Publish:** Filament Action-Button im Editor
- **Auto-Save:** Livewire-basiert (Filament speichert Formulardaten automatisch bei Bedarf)

---

## 7. URL-Struktur

### Frontend (Blade-Routen)

| Route | Blade View | Beschreibung |
|---|---|---|
| `/` | `blog.index` | Homepage / Blog-Feed |
| `/blog/{slug}` | `blog.show` | Einzelner Blog-Post |
| `/changelog` | `changelog.index` | Changelog-Übersicht |
| `/changelog/{slug}` | `changelog.show` | Einzelner Changelog-Eintrag |
| `/kategorie/{slug}` | `category.show` | Posts nach Kategorie |
| `/tag/{slug}` | `tag.show` | Posts nach Tag |
| `/seite/{slug}` | `page.show` | Statische Seite |

### Admin (Filament-Routen, automatisch generiert)

| Route | Filament Resource / Page | Beschreibung |
|---|---|---|
| `/admin` | Filament Dashboard | Dashboard mit Widgets |
| `/admin/posts` | PostResource::index | Post-Liste (Filament Table) |
| `/admin/posts/create` | PostResource::create | Neuen Post erstellen |
| `/admin/posts/{id}/edit` | PostResource::edit | Post bearbeiten |
| `/admin/categories` | CategoryResource::index | Kategorien verwalten |
| `/admin/tags` | TagResource::index | Tags verwalten |
| `/admin/media` | MediaResource::index | Media-Manager |
| `/admin/settings` | Custom Filament Page | Einstellungen |

### Sonstige Routen

| Route | Beschreibung |
|---|---|
| `/feed` | RSS-Feed (XML) |
| `/kategorie/{slug}/feed` | Kategorie-RSS-Feed |
| `/changelog/feed` | Changelog-RSS-Feed |
| `/sitemap.xml` | Sitemap |
| `/robots.txt` | Robots |
| `/preview/{post}` | Signed Draft-Preview |

---

## 8. Frontend / Design

### Designprinzipien

- Minimalistisch, typografiezentriert
- Kein visueller Overload – Whitespace als Gestaltungselement
- Clean Fonts: Inter (Body) + JetBrains Mono (Code) – self-hosted
- Dark Mode via Tailwind `dark:` Variant + Alpine.js Toggle mit `localStorage`
- Mobile-First, responsive
- Schnelle Seitenwechsel via `<a>` mit optionalem Livewire `wire:navigate` (SPA-ähnlich)

### Tailwind CSS Theming

```css
/* resources/css/app.css */
@import 'tailwindcss';

@layer base {
    :root {
        --color-prose-body: theme('colors.zinc.700');
        --color-prose-headings: theme('colors.zinc.900');
        --color-code-bg: theme('colors.zinc.100');
    }

    .dark {
        --color-prose-body: theme('colors.zinc.300');
        --color-prose-headings: theme('colors.zinc.100');
        --color-code-bg: theme('colors.zinc.800');
    }
}
```

### Blade Component-Architektur

```
resources/
├── views/
│   ├── components/
│   │   ├── blog-layout.blade.php        # Public Layout (minimal)
│   │   ├── navigation.blade.php         # Hauptnavigation
│   │   ├── footer.blade.php             # Footer
│   │   ├── post-card.blade.php          # Post-Teaser in der Liste
│   │   ├── post-meta.blade.php          # Datum, Lesezeit, Kategorie
│   │   ├── related-posts.blade.php      # Verwandte Posts
│   │   ├── breadcrumbs.blade.php        # Breadcrumb-Navigation
│   │   ├── pagination.blade.php         # Pagination
│   │   ├── dark-mode-toggle.blade.php   # Alpine.js Dark Mode Switch
│   │   ├── changelog/
│   │   │   ├── timeline-item.blade.php  # Timeline-Eintrag
│   │   │   └── type-badge.blade.php     # added/changed/fixed Badge
│   │   └── seo/
│   │       ├── meta-tags.blade.php      # OG Tags, Twitter Cards
│   │       └── structured-data.blade.php # JSON-LD
│   ├── blog/
│   │   ├── index.blade.php             # Blog-Feed
│   │   └── show.blade.php              # Einzelner Post
│   ├── changelog/
│   │   ├── index.blade.php             # Changelog-Timeline
│   │   └── show.blade.php              # Einzelner Eintrag
│   ├── category/
│   │   └── show.blade.php              # Posts nach Kategorie
│   ├── tag/
│   │   └── show.blade.php              # Posts nach Tag
│   ├── page/
│   │   └── show.blade.php              # Statische Seite
│   └── feeds/
│       ├── rss.blade.php               # RSS 2.0 Template
│       └── atom.blade.php              # Atom Feed Template
├── css/
│   └── app.css                          # Tailwind Entry Point
└── js/
    └── app.js                           # Alpine.js + Dark Mode (minimal)
```

### Seitenstruktur

**Homepage:**
- Blog-Name + kurze Tagline
- Post-Liste als `<x-post-card>` Komponenten (Titel, Datum, Excerpt, Kategorie)
- Pagination via Laravel Paginator (Blade-Links)
- Optional: Livewire `wire:navigate` für SPA-ähnliches Navigieren

**Post-Ansicht:**
- Titel, Datum, Lesezeit, Kategorie(n)
- Optional: Table of Contents (auto-generiert aus Headings, serverseitig via PHP)
- HTML-Content (direkt aus `body` Feld)
- Code-Blöcke mit Shiki-Highlighting
- Tags am Ende
- Vorheriger/Nächster Post Navigation

**Changelog-Ansicht:**
- Timeline-Layout (Datum prominent)
- Gruppierung nach Monat/Jahr
- Farbcodierte `<x-changelog.type-badge>` (added = grün, changed = blau, fixed = gelb, removed = rot)

### Dark Mode (Alpine.js)

```blade
{{-- resources/views/components/dark-mode-toggle.blade.php --}}
<div x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
     x-init="$watch('dark', val => {
         localStorage.setItem('theme', val ? 'dark' : 'light');
         document.documentElement.classList.toggle('dark', val);
     })"
     x-on:click="dark = !dark"
     class="cursor-pointer">
    <x-heroicon-o-sun x-show="dark" class="w-5 h-5" />
    <x-heroicon-o-moon x-show="!dark" class="w-5 h-5" />
</div>
```

---

## 9. Sicherheit & Datenschutz

- **DSGVO-konform:** Kein Google Analytics, keine externen Fonts (self-hosted), keine Cookies (außer Auth-Session)
- **Content Security Policy:** Strenge CSP-Header
- **XSS-Schutz:** Blade escaped standardmäßig (`{{ }}`); `{!! !!}` nur für sanitisierten HTML-Content aus dem Editor
- **HTML Sanitization:** Editor-Output wird beim Speichern via HTMLPurifier bereinigt
- **CSRF-Schutz:** Laravel-Standard (`@csrf` in Forms, Livewire automatisch)
- **Rate Limiting:** Auf Login und API-Endpunkte
- **SQLite-Backup:** Einfaches Backup durch Kopieren der DB-Datei
- **Signed URLs:** Für Draft-Previews (kein öffentlicher Zugriff auf Drafts)
- **Kein externer Kommentar-Dienst:** Bewusst keine Kommentare
- **Filament Auth Guard:** Separater Guard für Admin-Bereich

---

## 10. Performance-Ziele

| Metrik | Ziel |
|---|---|
| First Contentful Paint | < 0.8s |
| Largest Contentful Paint | < 1.2s |
| Total JS (gzipped) | < 30 KB (Alpine.js + minimal Custom) |
| Total Page Size | < 100 KB (ohne Bilder) |
| Lighthouse Score | > 97 |
| SQLite Query Time | < 10ms pro Request |
| Time to First Byte | < 100ms |

### Performance-Strategien

- **Server-Rendered HTML** – Kein Client-Side-Rendering, sofort sichtbar
- **Minimales JS** – Alpine.js (~15KB gzipped) statt React (~140KB) + Inertia + Lexical
- **Filament nur im Admin** – Frontend hat keinen Filament/Livewire-Overhead
- **Tailwind Purge** – Nur genutzte Klassen im Build (Tailwind CSS 4 automatic content detection)
- **Self-hosted Fonts** – Kein Google Fonts CDN, Subset mit `font-display: swap`
- **Image Lazy Loading** – Native `loading="lazy"` auf Bilder
- **Shiki serverseitig** – Code-Highlighting ohne clientseitiges JS
- **Route Caching** – `php artisan route:cache` für Production
- **View Caching** – `php artisan view:cache` für Production
- **Optional: `wire:navigate`** – SPA-ähnliche Navigation ohne Full-Page-Reload (Livewire 3 Feature)

---

## 11. Mögliche Erweiterungen (v2+)

- **Suche:** SQLite FTS5 Volltextsuche mit Livewire-Komponente
- **Newsletter:** E-Mail-Abo mit Double-Opt-In
- **Webmentions:** IndieWeb-kompatibel
- **Reading List / Bookmarks:** Kuratierte Link-Liste
- **Image Optimization Pipeline:** Automatisches WebP/AVIF via Intervention Image
- **Multi-Language:** i18n Support (Laravel Lang)
- **Syntax-Theme-Switcher:** Verschiedene Code-Themes (Light/Dark)
- **Comments (optional):** Eigene Livewire-Komponente ohne Drittanbieter
- **filament-tiptap-editor:** Upgrade auf erweiterten Block-Editor falls nötig
- **Filament Shield:** Rollen-/Rechte-Verwaltung falls Multi-User gewünscht

---

## 12. Projektstruktur

```
blog/
├── app/
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── PostResource.php           # Post CRUD (Form + Table)
│   │   │   ├── PostResource/
│   │   │   │   └── Pages/
│   │   │   │       ├── ListPosts.php
│   │   │   │       ├── CreatePost.php
│   │   │   │       └── EditPost.php
│   │   │   ├── CategoryResource.php       # Category CRUD
│   │   │   ├── TagResource.php            # Tag CRUD
│   │   │   └── MediaResource.php          # Media CRUD
│   │   ├── Pages/
│   │   │   ├── Dashboard.php              # Custom Dashboard
│   │   │   └── Settings.php               # Blog-Einstellungen
│   │   └── Widgets/
│   │       ├── StatsOverview.php          # Post-Count, Drafts etc.
│   │       └── LatestPosts.php            # Letzte Veröffentlichungen
│   ├── Http/
│   │   └── Controllers/
│   │       ├── BlogController.php         # Blog-Feed + Einzelpost
│   │       ├── ChangelogController.php    # Changelog
│   │       ├── CategoryController.php     # Kategorie-Ansicht
│   │       ├── TagController.php          # Tag-Ansicht
│   │       ├── PageController.php         # Statische Seiten
│   │       ├── FeedController.php         # RSS/Atom Feeds
│   │       └── SitemapController.php      # Sitemap
│   ├── Models/
│   │   ├── Post.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Media.php
│   │   └── Setting.php
│   └── Services/
│       ├── MarkdownService.php            # HTML→Markdown Export (für RSS)
│       └── SeoService.php                 # Structured Data, Meta-Tags
├── database/
│   ├── database.sqlite
│   └── migrations/
├── resources/
│   ├── views/
│   │   ├── components/                    # Blade Components
│   │   ├── blog/                          # Blog Views
│   │   ├── changelog/                     # Changelog Views
│   │   ├── category/                      # Kategorie Views
│   │   ├── tag/                           # Tag Views
│   │   ├── page/                          # Statische Seiten
│   │   └── feeds/                         # RSS/Atom Templates
│   ├── css/
│   │   └── app.css                        # Tailwind Entry
│   └── js/
│       └── app.js                         # Alpine.js (minimal)
├── routes/
│   └── web.php                            # Frontend-Routen
├── public/
│   ├── build/                             # Vite Build Output
│   └── uploads/                           # Media Uploads
└── vite.config.js
```

**Was wegfällt gegenüber v1.3:**
- `resources/js/Pages/` – Keine React Page Components
- `resources/js/Components/ui/` – Keine shadcn/ui Komponenten
- `resources/js/Components/Admin/Editor/` – Kein Lexical-Setup (7 Dateien → 0)
- `resources/js/Hooks/` – Keine React Hooks
- `resources/js/Lib/queryClient.js` – Kein TanStack Query
- `components.json` – Keine shadcn/ui Config
- `tsconfig.json` – Kein TypeScript
- `app/Http/Controllers/Admin/` – Kein Admin-Controller-Code (Filament übernimmt)
- `app/Services/LexicalService.php` – Kein Lexical JSON→HTML Service

---

## 13. Nächste Schritte

1. **Projektname festlegen**
2. **Laravel-Projekt aufsetzen** (`laravel new blog`)
3. **Filament installieren** (`composer require filament/filament && php artisan filament:install --panels`)
4. **Filament Admin-User erstellen** (`php artisan make:filament-user`)
5. **SQLite konfigurieren** + Migrations erstellen
6. **Models + Relationships** definieren
7. **Filament Resources erstellen** (`php artisan make:filament-resource Post --generate`)
8. **RichEditor konfigurieren** (Toolbar-Buttons, Bild-Upload)
9. **Filament Dashboard-Widgets** erstellen
10. **Frontend Blade-Views** bauen (Blog, Changelog, Statische Seiten)
11. **Blade Components** erstellen (Layout, PostCard, Navigation, Footer, DarkMode)
12. **Code-Highlighting** mit Shiki integrieren
13. **SEO** implementieren (Meta-Tags, Structured Data, Sitemap)
14. **RSS-Feeds** implementieren
15. **Dark Mode** implementieren (Alpine.js Toggle)
16. **Deployment vorbereiten** (Standard PHP-Hosting, kein Node.js nötig)

---

## 14. Offene Entscheidungen

| Frage | Optionen |
|---|---|
| Projektname | TBD |
| Hosting | VPS vs. Shared Hosting (beides möglich, kein Node.js nötig) |
| RichEditor | Filament Standard-RichEditor vs. `awcodes/filament-tiptap-editor` (erweitert) |
| Media-Verwaltung | Eigenes MediaResource vs. `filament/spatie-laravel-media-library-plugin` |
| Livewire Frontend? | Reine Blade-Views vs. `wire:navigate` für SPA-ähnliche Navigation |
| Settings-Speicherung | `spatie/laravel-settings` vs. eigene Settings-Tabelle |

### Entschieden ✅

| Entscheidung | Wahl | Grund |
|---|---|---|
| Stack | TALL (Tailwind + Alpine.js + Laravel + Livewire) | Kein JS-Framework-Overhead, PHP-only |
| Admin Panel | Filament 3 | Komplettes Admin out-of-the-box |
| WYSIWYG Editor | Filament RichEditor (TipTap) | Integriert, kein Lexical/React nötig |
| Styling | Tailwind CSS 4 | Konsistent in Frontend + Filament |
| Code-Highlighting | Shiki (serverseitig, zero JS) | Beibehalten aus v1.3 |
| Icons | Blade Heroicons | Filament-Standard, konsistent |
| ~~React/Inertia/shadcn~~ | Entfernt | Durch TALL Stack + Filament ersetzt |
| ~~Lexical (Meta)~~ | Entfernt | Durch Filament RichEditor ersetzt |
| ~~TanStack Query/Table~~ | Entfernt | Durch Filament Table/Livewire ersetzt |
