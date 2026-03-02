# Blog Platform – Spezifikation

**Projekt:** Neues eigenständiges Blog-Projekt
**Tech-Stack:** Laravel 12 + Inertia.js + React + TanStack
**Erstellt:** 28. Februar 2026
**Status:** Draft v1.3

---

## 1. Projektübersicht

### Vision

Ein minimalistischer, content-fokussierter Blog mit klassischen Blog-Features und Dev-Blog/Changelog-Charakter. Markdown-first, privacy-bewusst, schnell und schlank – mit modernem SPA-Feeling durch Inertia.js und React.

### Kernprinzipien

- **Content First** – Der Inhalt steht im Mittelpunkt, kein visueller Ballast
- **WYSIWYG-Editor** – Rich-Text-Editing via Lexical (Facebook), Markdown-Import/Export beibehalten
- **Modern SPA-Feel** – Kein Full-Page-Reload dank Inertia.js, aber kein separates API nötig
- **Performance** – SQLite als Datenbank, minimale Dependencies, SSR-ready
- **SEO-optimiert** – Server-Side Rendering via Inertia SSR, Structured Data, saubere URLs
- **Privacy-First** – DSGVO-konform, keine externen Tracker

---

## 2. Tech-Stack

### Backend

| Komponente | Technologie |
|---|---|
| Framework | Laravel 12 |
| Datenbank | SQLite |
| Adapter | Inertia.js (Server-Side) |
| Markdown Parser | league/commonmark (mit GFM Extensions) |
| Auth | Laravel Breeze (Inertia/React Scaffold) |

### Frontend

| Komponente | Technologie |
|---|---|
| UI Framework | React 19 |
| Routing/Adapter | Inertia.js (Client-Side) |
| UI Components | shadcn/ui (Radix UI Primitives + Tailwind) |
| Styling | Tailwind CSS 4 |
| Server State | TanStack Query (React Query) |
| Tabellen | TanStack Table (Admin-Bereich) + shadcn/ui DataTable |
| Build Tool | Vite |
| Code Highlighting | Shiki (serverseitig, zero JS) |
| WYSIWYG Editor | Lexical (Facebook) – Rich-Text-Editor mit Plugin-Architektur |
| Icons | Lucide React (shadcn/ui Default) |
| Theming | CSS Custom Properties via shadcn/ui Theme System |

### shadcn/ui – Warum?

shadcn/ui ist keine klassische Component Library, sondern eine Sammlung von kopierbaren, anpassbaren Komponenten auf Basis von Radix UI + Tailwind CSS. Vorteile:

- **Keine Dependency** – Komponenten werden direkt ins Projekt kopiert (`npx shadcn@latest add button`)
- **Volle Kontrolle** – Jede Komponente ist editierbar, kein CSS-Override-Kampf
- **Radix UI Primitives** – Zugänglichkeit (a11y) und Keyboard-Navigation out-of-the-box
- **Tailwind-native** – Nahtlose Integration, konsistentes Design-Token-System
- **Dark Mode built-in** – Theme-Switcher über CSS Custom Properties
- **TanStack Table Integration** – shadcn/ui bietet fertige DataTable-Patterns auf TanStack Table-Basis

### shadcn/ui Komponenten-Plan

**Admin-Panel:**

- `Button`, `Input`, `Textarea`, `Label`, `Select` – Formulare
- `Dialog`, `Sheet` – Modale & Slide-Over Panels
- `DropdownMenu`, `Command` – Aktionsmenüs & Command Palette
- `Table` + `DataTable` – Post-Liste (TanStack Table Integration)
- `Tabs` – Editor-Bereiche (Content / SEO / Settings)
- `Badge` – Status (Draft/Published), Changelog-Typen
- `Toast` / `Sonner` – Notifications (Auto-Save, Publish-Bestätigung)
- `Card` – Dashboard-Widgets
- `Accordion` – SEO-Panel, Settings-Gruppen
- `Switch`, `Checkbox` – Einstellungen
- `Calendar`, `Popover` – Date-Picker für Scheduled Publishing
- `Separator`, `Skeleton` – Layout & Loading States
- `AlertDialog` – Lösch-Bestätigungen

**Frontend (Blog):**

- `NavigationMenu` – Hauptnavigation
- `Badge` – Tags, Kategorien
- `Separator` – Visuelle Trenner
- `Breadcrumb` – Breadcrumb-Navigation
- `Toggle` – Dark Mode Switch
- Minimaler Einsatz – Frontend bleibt bewusst schlank und custom

### Inertia.js – Warum?

Inertia.js ist die Brücke zwischen Laravel und React. Vorteile:

- **Kein separates API nötig** – Laravel-Controller liefern Daten direkt als Props an React-Komponenten
- **SPA-Feeling** – Client-Side Navigation ohne Full-Page-Reloads
- **Laravel-Routing bleibt** – Keine doppelte Routing-Logik
- **SSR-Support** – Server-Side Rendering für SEO (kritisch für einen Blog!)
- **Forms & Validation** – Laravel-Validation-Errors landen automatisch in React
- **Shared Data** – Auth-Status, Flash Messages etc. global verfügbar

### TanStack – Einsatzbereiche

| Library | Einsatz |
|---|---|
| **TanStack Query** | Async-Daten im Admin (Auto-Save, Optimistic Updates, Draft-Polling, Media-Upload-Status) |
| **TanStack Table** | Post-Liste im Admin (Sortierung, Filterung, Pagination, Bulk-Actions) |
| **TanStack Form** (optional) | Komplexe Formulare im Admin (Post-Editor, Settings) |

**Wichtig:** Inertia.js und TanStack Query haben überlappende Zuständigkeiten. Klare Abgrenzung:

- **Inertia** = Page-Navigation, initiale Daten, Formulare (Standard-Workflow)
- **TanStack Query** = Hintergrund-Daten, Polling, optimistic Updates, komplexe Admin-Interaktionen

---

## 3. Datenmodell

### Posts

| Feld | Typ | Beschreibung |
|---|---|---|
| `id` | integer (PK) | Auto-Increment |
| `title` | string(255) | Titel des Beitrags |
| `slug` | string(255) | URL-Slug (unique) |
| `excerpt` | text (nullable) | Kurzbeschreibung / Teaser |
| `body` | text (JSON) | Lexical EditorState (JSON-serialisiert) |
| `body_html` | text | Gerendertes HTML (Cache, beim Speichern generiert) |
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

## 5. Architektur – Inertia.js Flow

### Grundprinzip

```
Browser Request
    → Laravel Router
        → Controller (bereitet Daten auf)
            → Inertia::render('Blog/Show', ['post' => $post])
                → React-Komponente empfängt Props
                    → Rendert UI
```

### Datenfluss-Beispiel: Blog-Post laden

```php
// Laravel Controller
class BlogController extends Controller
{
    public function show(Post $post)
    {
        return Inertia::render('Blog/Show', [
            'post' => $post->load(['categories', 'tags']),
            'related' => $post->relatedPosts(3),
        ]);
    }
}
```

```jsx
// React Page Component
export default function Show({ post, related }) {
    return (
        <BlogLayout>
            <article>
                <h1>{post.title}</h1>
                <div dangerouslySetInnerHTML={{ __html: post.body_html }} />
            </article>
            <RelatedPosts posts={related} />
        </BlogLayout>
    );
}
```

### Datenfluss-Beispiel: Admin Post-Editor mit Lexical + Auto-Save

```jsx
// Admin Post Editor – Lexical + TanStack Query für Auto-Save
import { useMutation } from '@tanstack/react-query';
import { useForm } from '@inertiajs/react';
import { LexicalComposer } from '@lexical/react/LexicalComposer';
import { RichTextPlugin } from '@lexical/react/LexicalRichTextPlugin';
import { ContentEditable } from '@lexical/react/LexicalContentEditable';
import { HistoryPlugin } from '@lexical/react/LexicalHistoryPlugin';
import { MarkdownShortcutPlugin } from '@lexical/react/LexicalMarkdownShortcutPlugin';
import { OnChangePlugin } from '@lexical/react/LexicalOnChangePlugin';
import { TRANSFORMERS } from '@lexical/markdown';
import { LexicalErrorBoundary } from '@lexical/react/LexicalErrorBoundary';
import { ToolbarPlugin } from '@/Components/Admin/Editor/ToolbarPlugin';
import { ImagePlugin } from '@/Components/Admin/Editor/ImagePlugin';
import { AutoSavePlugin } from '@/Components/Admin/Editor/AutoSavePlugin';
import { editorTheme } from '@/Components/Admin/Editor/EditorTheme';

export default function PostEditor({ post }) {
    // Inertia-Form für Submit (Titel, Status etc.)
    const { data, setData, put, processing, errors } = useForm({
        title: post.title,
        body: post.body,           // Lexical EditorState JSON
        status: post.status,
    });

    // Lexical Config – EditorState aus gespeichertem JSON laden
    const initialConfig = {
        namespace: 'BlogEditor',
        theme: editorTheme,
        editorState: post.body,    // JSON-String → EditorState
        onError: (error) => console.error(error),
        nodes: [/* ImageNode, CodeNode, TableNode, ... */],
    };

    return (
        <form onSubmit={(e) => { e.preventDefault(); put(`/admin/posts/${post.id}`); }}>
            <LexicalComposer initialConfig={initialConfig}>
                <ToolbarPlugin />
                <RichTextPlugin
                    contentEditable={<ContentEditable className="prose dark:prose-invert" />}
                    ErrorBoundary={LexicalErrorBoundary}
                />
                <HistoryPlugin />
                <MarkdownShortcutPlugin transformers={TRANSFORMERS} />
                <ImagePlugin />
                <AutoSavePlugin postId={post.id} debounceMs={5000} />
            </LexicalComposer>
        </form>
    );
}
```

---

## 6. Features

### 6.1 WYSIWYG-Editor (Lexical)

**Warum Lexical?**

Lexical ist ein erweiterbares Text-Editor-Framework von Meta/Facebook. Es ersetzt den einfachen Textarea+Preview-Ansatz durch einen vollwertigen WYSIWYG-Editor mit Plugin-Architektur.

- **Framework:** Lexical (`lexical` + `@lexical/react`) – offizielle React-Bindings
- **Architektur:** Plugin-basiert, immutable State-Model, built-in Undo/Redo
- **Rich-Text-Features:** Headings, Bold/Italic/Underline/Strikethrough, Links, Listen (ordered/unordered), Blockquotes, horizontale Linien
- **Code-Blocks:** `@lexical/code` Plugin mit Syntax-Highlighting (clientseitig im Editor, serverseitig via Shiki für Ausgabe)
- **Tabellen:** `@lexical/table` Plugin – visuelles Erstellen und Bearbeiten
- **Bilder:** Custom ImageNode – Drag & Drop, Resize, Alt-Text-Editing inline
- **Markdown-Kompatibilität:** `@lexical/markdown` Plugin – Import/Export von Markdown, Markdown-Shortcuts im Editor (z.B. `#` für Heading, `**` für Bold)
- **HTML-Export:** `@lexical/html` – Generierung von HTML aus EditorState
- **Toolbar:** Floating Toolbar + feste Toolbar (shadcn/ui Komponenten für Buttons, Dropdowns, Popovers)
- **Accessibility:** WCAG-konform, vollständige Keyboard-Navigation

**Speicher-Strategie:**

| Feld | Inhalt | Beschreibung |
|---|---|---|
| `body` | Lexical EditorState (JSON) | Primäre Datenquelle, lossless |
| `body_html` | Gerendertes HTML | Cache für Frontend-Ausgabe, beim Speichern generiert |
| `body_markdown` | Markdown-Export | Optional, für Portabilität/RSS-Feeds |

**Lexical-Plugins im Einsatz:**

| Plugin | Package | Funktion |
|---|---|---|
| RichTextPlugin | `@lexical/react` | Basis Rich-Text-Editing |
| HistoryPlugin | `@lexical/react` | Undo/Redo (Ctrl+Z/Y) |
| MarkdownShortcutPlugin | `@lexical/react` | Markdown-Shortcuts beim Tippen |
| ListPlugin | `@lexical/list` | Ordered/Unordered Lists, Checklisten |
| LinkPlugin | `@lexical/link` | Links einfügen/bearbeiten |
| TablePlugin | `@lexical/table` | Tabellen erstellen/bearbeiten |
| CodeHighlightPlugin | `@lexical/code` | Code-Blöcke mit Sprach-Auswahl |
| AutoFocusPlugin | `@lexical/react` | Auto-Focus beim Öffnen |
| OnChangePlugin | `@lexical/react` | State-Change-Listener für Auto-Save |
| ImagePlugin | Custom | Bild-Upload, Resize, Alt-Text |
| DragDropPastePlugin | Custom | Bilder per Drag & Drop / Paste einfügen |

**Serverseitige Verarbeitung (Laravel):**

- **HTML-Generierung:** Lexical JSON → HTML-Konvertierung beim Speichern (via Node.js Micro-Service oder `@lexical/html` in SSR-Prozess)
- **Code-Highlighting:** Shiki wird auf die generierten HTML-Code-Blöcke angewendet
- **Fallback:** league/commonmark bleibt für Markdown-Import (bestehende Inhalte, Import-Funktion)
- **Sanitization:** HTML-Output wird serverseitig sanitized bevor er in `body_html` gespeichert wird

### 6.2 SEO-Optimierung

- **Inertia SSR:** Server-Side Rendering für vollständiges HTML bei Crawlern
- **`<Head>`-Komponente:** Inertia's `<Head>` für dynamische Meta-Tags pro Seite
- **Meta-Tags:** `<title>`, `<meta description>`, Open Graph, Twitter Cards
- **Structured Data:** JSON-LD für `BlogPosting` und `Article`
- **Canonical URLs:** Automatisch generiert
- **Sitemap:** Auto-generierte `sitemap.xml` (reines Laravel, kein Inertia)
- **Robots.txt:** Konfigurierbar
- **Saubere URLs:** `/blog/mein-erster-post`, `/changelog`, `/kategorie/laravel`
- **Breadcrumbs:** Structured Data + visuelle Breadcrumbs als React-Komponente

### 6.3 RSS-Feed

- **Haupt-Feed:** `/feed` – Alle veröffentlichten Posts
- **Kategorie-Feeds:** `/kategorie/{slug}/feed`
- **Changelog-Feed:** `/changelog/feed`
- **Format:** RSS 2.0 und Atom
- **Content:** Volltext oder Excerpt (konfigurierbar im Admin)
- **Auto-Discovery:** `<link rel="alternate">` via Inertia `<Head>`
- **Wichtig:** RSS-Routen liefern XML direkt (kein Inertia-Response)

### 6.4 Admin-Panel / Dashboard

#### Authentifizierung

- Laravel Breeze (Inertia/React Starter Kit)
- Single-User Login
- Remember Me + Session-basiert

#### Dashboard

- Übersicht: Post-Count, Drafts, letzte Veröffentlichungen
- Quick-Actions: Neuen Post erstellen, letzten Draft fortsetzen
- React-Komponenten mit Inertia Shared Data

#### Post-Editor

- **WYSIWYG-Editor:** Lexical Rich-Text-Editor mit Toolbar (Formatting, Links, Bilder, Code-Blöcke, Tabellen)
- **Markdown-Shortcuts:** Inline-Markdown-Syntax (z.B. `# `, `**text**`, `- `) wird automatisch in Rich-Text konvertiert
- **Drag & Drop:** Bilder direkt in den Editor ziehen oder per Paste einfügen
- **Floating Toolbar:** Kontextsensitive Formatting-Optionen bei Text-Selektion
- **Auto-Save:** TanStack Query Mutation (debounced, speichert Lexical EditorState als JSON)
- **Slug-Generator:** Automatisch aus Titel, manuell überschreibbar
- **SEO-Panel:** Akkordeon mit Meta-Title, Meta-Description, OG-Image
- **Kategorie-/Tag-Picker:** Combobox mit Inline-Erstellen
- **Publish-Flow:** Draft speichern → Preview → Publish (mit optionalem Datum)

#### Post-Liste (TanStack Table)

- Sortierbar nach Titel, Datum, Status, Typ
- Filterbar nach Status (Draft/Published), Typ (Post/Changelog/Page)
- Suche über Titel
- Bulk-Actions: Löschen, Status ändern
- Pagination (Server-Side via Inertia)

#### Media-Manager

- Drag & Drop Upload
- Bildvorschau-Grid
- TanStack Query für Upload-Progress und optimistische UI-Updates
- Markdown-Snippet-Kopieren (`![alt](url)`)

#### Einstellungen

- Blog-Name, Beschreibung, Tagline
- Social Links
- Feed-Optionen (Volltext/Excerpt)
- SEO-Defaults

### 6.5 Draft/Published Workflow

- **Status:** `draft` → `published`
- **Scheduled Publishing:** `published_at` in der Zukunft → automatische Veröffentlichung (Laravel Scheduler: `php artisan schedule:run`)
- **Preview:** Draft-Posts über signed Preview-URL aufrufbar (`/preview/{post}?signature=...`)
- **Quick Publish:** Ein-Klick aus dem Editor
- **Auto-Save:** Debounced Background-Save via TanStack Query (kein Datenverlust)

---

## 7. URL-Struktur

### Frontend (Inertia-Routen)

| Route | React Page Component | Beschreibung |
|---|---|---|
| `/` | `Pages/Blog/Index` | Homepage / Blog-Feed |
| `/blog/{slug}` | `Pages/Blog/Show` | Einzelner Blog-Post |
| `/changelog` | `Pages/Changelog/Index` | Changelog-Übersicht |
| `/changelog/{slug}` | `Pages/Changelog/Show` | Einzelner Changelog-Eintrag |
| `/kategorie/{slug}` | `Pages/Category/Show` | Posts nach Kategorie |
| `/tag/{slug}` | `Pages/Tag/Show` | Posts nach Tag |
| `/seite/{slug}` | `Pages/Page/Show` | Statische Seite |

### Admin (Inertia-Routen, Auth-geschützt)

| Route | React Page Component | Beschreibung |
|---|---|---|
| `/admin` | `Pages/Admin/Dashboard` | Dashboard |
| `/admin/posts` | `Pages/Admin/Posts/Index` | Post-Liste (TanStack Table) |
| `/admin/posts/create` | `Pages/Admin/Posts/Create` | Neuen Post erstellen |
| `/admin/posts/{id}/edit` | `Pages/Admin/Posts/Edit` | Post bearbeiten |
| `/admin/categories` | `Pages/Admin/Categories/Index` | Kategorien verwalten |
| `/admin/tags` | `Pages/Admin/Tags/Index` | Tags verwalten |
| `/admin/media` | `Pages/Admin/Media/Index` | Media-Manager |
| `/admin/settings` | `Pages/Admin/Settings/Index` | Einstellungen |

### Nicht-Inertia-Routen (reines Laravel)

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
- Dark Mode Support via shadcn/ui Theme System (CSS Custom Properties + `next-themes`-Pattern)
- shadcn/ui Neutral-Theme als Basis, angepasst an Blog-Ästhetik
- Mobile-First, responsive
- Smooth Page Transitions (Inertia `<Link>` mit Progress-Indicator)

### Tailwind CSS + shadcn/ui Theming

```css
/* globals.css – shadcn/ui Theme Variables */
@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 240 10% 3.9%;
    --primary: 240 5.9% 10%;
    --muted: 240 4.8% 95.9%;
    --accent: 240 4.8% 95.9%;
    --border: 240 5.9% 90%;
    --radius: 0.5rem;
    /* Blog-spezifisch */
    --prose-body: 240 10% 20%;
    --prose-headings: 240 10% 3.9%;
    --code-bg: 240 5% 96%;
  }
  .dark {
    --background: 240 10% 3.9%;
    --foreground: 0 0% 98%;
    /* ... Dark Mode Overrides */
  }
}
```

### React Component-Architektur

```
resources/js/
├── Pages/
│   ├── Blog/
│   │   ├── Index.jsx          # Blog-Feed
│   │   └── Show.jsx           # Einzelner Post
│   ├── Changelog/
│   │   ├── Index.jsx          # Changelog-Timeline
│   │   └── Show.jsx           # Einzelner Eintrag
│   ├── Category/
│   │   └── Show.jsx           # Posts nach Kategorie
│   ├── Tag/
│   │   └── Show.jsx           # Posts nach Tag
│   ├── Page/
│   │   └── Show.jsx           # Statische Seite
│   └── Admin/
│       ├── Dashboard.jsx
│       ├── Posts/
│       │   ├── Index.jsx      # Post-Liste (shadcn DataTable)
│       │   ├── Create.jsx
│       │   └── Edit.jsx       # Editor mit Auto-Save
│       ├── Categories/
│       │   └── Index.jsx
│       ├── Tags/
│       │   └── Index.jsx
│       ├── Media/
│       │   └── Index.jsx      # Media-Manager
│       └── Settings/
│           └── Index.jsx
├── Components/
│   ├── ui/                    # shadcn/ui Komponenten (auto-generiert)
│   │   ├── button.jsx
│   │   ├── input.jsx
│   │   ├── badge.jsx
│   │   ├── card.jsx
│   │   ├── dialog.jsx
│   │   ├── dropdown-menu.jsx
│   │   ├── table.jsx
│   │   ├── tabs.jsx
│   │   ├── toast.jsx
│   │   ├── command.jsx
│   │   ├── accordion.jsx
│   │   ├── calendar.jsx
│   │   ├── popover.jsx
│   │   ├── separator.jsx
│   │   ├── skeleton.jsx
│   │   ├── switch.jsx
│   │   ├── navigation-menu.jsx
│   │   ├── breadcrumb.jsx
│   │   └── ...
│   ├── Blog/
│   │   ├── PostCard.jsx       # Post-Teaser in der Liste
│   │   ├── PostMeta.jsx       # Datum, Lesezeit, Kategorie
│   │   └── TableOfContents.jsx
│   ├── Changelog/
│   │   ├── TimelineItem.jsx
│   │   └── TypeBadge.jsx      # shadcn Badge: added/changed/fixed
│   ├── Admin/
│   │   ├── Editor/
│   │   │   ├── LexicalEditor.jsx      # Lexical Composer + Plugin-Setup
│   │   │   ├── ToolbarPlugin.jsx      # Feste Toolbar (shadcn Buttons/Dropdowns)
│   │   │   ├── FloatingToolbar.jsx    # Floating Formatting bei Text-Selektion
│   │   │   ├── ImagePlugin.jsx        # Bild-Upload, Resize, Alt-Text
│   │   │   ├── DragDropPastePlugin.jsx # Bilder per Drag & Drop / Paste
│   │   │   ├── CodeBlockPlugin.jsx    # Code-Block mit Sprach-Auswahl
│   │   │   ├── AutoSavePlugin.jsx     # OnChange → TanStack Mutation (debounced)
│   │   │   └── EditorTheme.js         # Lexical Theme (Tailwind-Klassen)
│   │   ├── PostDataTable.jsx  # shadcn DataTable + TanStack Table
│   │   ├── MediaUploader.jsx  # Drag & Drop mit shadcn Dialog
│   │   ├── SeoPanel.jsx       # shadcn Accordion mit Formfeldern
│   │   ├── PublishDatePicker.jsx  # shadcn Calendar + Popover
│   │   └── StatusBadge.jsx    # Draft/Published Badge
│   ├── Layout/
│   │   ├── BlogLayout.jsx     # Public Layout (minimal)
│   │   ├── AdminLayout.jsx    # Admin Layout mit Sidebar (shadcn Sheet)
│   │   ├── Navigation.jsx     # shadcn NavigationMenu
│   │   ├── Footer.jsx
│   │   └── ThemeProvider.jsx  # Dark Mode Provider
│   └── shared/
│       ├── Breadcrumbs.jsx    # shadcn Breadcrumb
│       ├── Pagination.jsx
│       └── ProgressBar.jsx    # Inertia Page-Load Indicator
├── Hooks/
│   ├── useAutoSave.js         # Debounced Auto-Save Hook
│   ├── useTheme.js            # Dark Mode Hook
│   └── useReadingTime.js
├── Lib/
│   ├── queryClient.js         # TanStack Query Client Config
│   └── utils.js               # cn() Helper (clsx + tailwind-merge)
└── app.jsx                    # Inertia App Entry Point + QueryClientProvider + ThemeProvider
```

### Seitenstruktur

**Homepage:**
- Blog-Name + kurze Tagline
- Post-Liste als `<PostCard>` Komponenten (Titel, Datum, Excerpt, Kategorie)
- Pagination via Inertia Links (kein Full-Reload)

**Post-Ansicht:**
- Titel, Datum, Lesezeit, Kategorie(n)
- Optional: Table of Contents (auto-generiert aus Headings)
- Markdown-Content (server-gerendertes HTML)
- Tags am Ende
- Vorheriger/Nächster Post Navigation

**Changelog-Ansicht:**
- Timeline-Layout (Datum prominent)
- Gruppierung nach Monat/Jahr
- Farbcodierte `<TypeBadge>` (added = grün, changed = blau, fixed = gelb, removed = rot)

---

## 9. Inertia SSR – SEO Setup

Server-Side Rendering ist kritisch für einen Blog. Ohne SSR sehen Crawler nur eine leere Seite.

### Konfiguration

```bash
# SSR-Server starten (Node.js Prozess)
php artisan inertia:start-ssr
```

### Inertia `<Head>` für SEO

```jsx
import { Head } from '@inertiajs/react';

export default function Show({ post }) {
    return (
        <>
            <Head>
                <title>{post.meta_title || post.title}</title>
                <meta name="description" content={post.meta_description || post.excerpt} />
                <meta property="og:title" content={post.title} />
                <meta property="og:description" content={post.excerpt} />
                <meta property="og:image" content={post.featured_image} />
                <meta property="og:type" content="article" />
                <link rel="canonical" href={`/blog/${post.slug}`} />
                <script type="application/ld+json">{JSON.stringify(post.structured_data)}</script>
            </Head>
            <article>{/* Post Content */}</article>
        </>
    );
}
```

### SSR Hosting-Hinweis

Inertia SSR benötigt Node.js auf dem Server. Für reine Shared-Hosting-Umgebungen ohne Node.js gibt es Alternativen:

- **Option A:** SSR via Node.js (empfohlen, benötigt VPS/Cloud)
- **Option B:** Pre-Rendering / Static-Page-Fallback für SEO-kritische Seiten
- **Option C:** `@inertiajs/server` mit Bun als lightweight Alternative

---

## 10. Sicherheit & Datenschutz

- **DSGVO-konform:** Kein Google Analytics, keine externen Fonts (self-hosted), keine Cookies (außer Auth-Session)
- **Content Security Policy:** Strenge CSP-Header
- **XSS-Schutz:** Markdown-Output wird serverseitig sanitized; React escaped standardmäßig
- **CSRF-Schutz:** Laravel-Standard (Inertia sendet CSRF-Token automatisch)
- **Rate Limiting:** Auf Login und API-Endpunkte
- **SQLite-Backup:** Einfaches Backup durch Kopieren der DB-Datei
- **Signed URLs:** Für Draft-Previews (kein öffentlicher Zugriff auf Drafts)
- **Kein externer Kommentar-Dienst:** Bewusst keine Kommentare

---

## 11. Performance-Ziele

| Metrik | Ziel |
|---|---|
| First Contentful Paint | < 1.0s |
| Largest Contentful Paint | < 1.5s |
| Total JS Bundle (gzipped) | < 80 KB |
| Total Page Size | < 150 KB (ohne Bilder) |
| Lighthouse Score | > 95 |
| SQLite Query Time | < 10ms pro Request |
| Inertia Page Navigation | < 200ms (nach Initial Load) |

### Performance-Strategien

- **Code-Splitting:** Vite + React Lazy für Admin vs. Frontend Bundle-Trennung (Lexical nur im Admin-Bundle)
- **Inertia Partial Reloads:** Nur geänderte Props nachladen
- **HTML-Cache:** Lexical JSON → HTML nur beim Speichern, nicht beim Laden
- **Tailwind Purge:** Nur genutzte Klassen im Build (Tailwind CSS 4 automatic content detection)
- **shadcn/ui = Zero Overhead:** Komponenten sind lokaler Code, kein Library-Bundle
- **Self-hosted Fonts:** Kein Google Fonts CDN, Subset mit `font-display: swap`
- **Image Lazy Loading:** Native `loading="lazy"` auf Bilder
- **Shiki serverseitig:** Code-Highlighting ohne clientseitiges JS

---

## 12. Mögliche Erweiterungen (v2+)

- **Suche:** SQLite FTS5 Volltextsuche mit React Combobox
- **Newsletter:** E-Mail-Abo mit Double-Opt-In
- **Webmentions:** IndieWeb-kompatibel
- **Reading List / Bookmarks:** Kuratierte Link-Liste
- **JSON-API:** Headless-Nutzung neben Inertia
- **Image Optimization Pipeline:** Automatisches WebP/AVIF via Intervention Image
- **Multi-Language:** i18n Support (Laravel Lang + React i18n)
- **Syntax-Theme-Switcher:** Verschiedene Code-Themes (Light/Dark)
- **Comments (optional):** Eigene Lösung ohne Drittanbieter
- **TanStack Router:** Falls Inertia-Routing nicht ausreicht (unwahrscheinlich)

---

## 13. Projektstruktur

```
blog/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── PostController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── TagController.php
│   │   │   │   ├── MediaController.php
│   │   │   │   └── SettingController.php
│   │   │   ├── BlogController.php
│   │   │   ├── ChangelogController.php
│   │   │   ├── FeedController.php
│   │   │   ├── PageController.php
│   │   │   └── SitemapController.php
│   │   └── Middleware/
│   │       └── HandleScheduledPosts.php
│   ├── Models/
│   │   ├── Post.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Media.php
│   │   └── Setting.php
│   └── Services/
│       ├── LexicalService.php     # Lexical JSON → HTML Konvertierung
│       ├── MarkdownService.php    # Markdown-Import/Export (Fallback + RSS)
│       └── SeoService.php
├── database/
│   ├── database.sqlite
│   └── migrations/
├── resources/
│   └── js/
│       ├── Pages/               # Inertia Page Components
│       │   ├── Blog/
│       │   ├── Changelog/
│       │   ├── Category/
│       │   ├── Tag/
│       │   ├── Page/
│       │   └── Admin/
│       ├── Components/          # Shared React Components
│       │   ├── Blog/
│       │   ├── Changelog/
│       │   ├── Admin/
│       │   ├── Layout/
│       │   └── UI/
│       ├── Hooks/               # Custom React Hooks
│       ├── Lib/                 # Utils, Query Client etc.
│       └── app.jsx              # Inertia Entry Point
├── routes/
│   └── web.php
├── public/
│   ├── build/                   # Vite Build Output
│   └── uploads/                 # Media Uploads
├── vite.config.js
├── tailwind.config.js
├── components.json              # shadcn/ui Konfiguration
└── tsconfig.json                # Optional: TypeScript
```

---

## 14. Nächste Schritte

1. **Projektname festlegen**
2. **Laravel-Projekt aufsetzen** (`laravel new blog --breeze --stack=react --ssr`)
3. **shadcn/ui initialisieren** (`npx shadcn@latest init` + Basis-Komponenten hinzufügen)
4. **Lexical installieren** (`npm install lexical @lexical/react @lexical/rich-text @lexical/list @lexical/link @lexical/code @lexical/table @lexical/markdown @lexical/html @lexical/selection @lexical/utils`)
5. **TanStack installieren** (`npm install @tanstack/react-query @tanstack/react-table`)
6. **SQLite konfigurieren** + Migrations erstellen
7. **Models + Relationships** definieren
8. **Layouts bauen** (BlogLayout + AdminLayout mit shadcn/ui Sidebar/Navigation)
9. **Lexical-Editor aufbauen** (LexicalComposer, Toolbar, Plugins, Theme)
10. **Admin-Panel aufbauen** (Dashboard, Post-Editor mit Lexical, DataTable)
11. **Frontend-Pages** erstellen (Blog, Changelog, Statische Seiten)
12. **HTML-Pipeline** implementieren (Lexical JSON → HTML + Shiki Code-Highlighting)
13. **SEO + SSR** konfigurieren + testen
14. **RSS-Feeds** implementieren (Markdown-Export aus Lexical für Feed-Content)
15. **Dark Mode** implementieren (ThemeProvider + shadcn/ui Theme Variables)
16. **Deployment vorbereiten** (VPS für SSR oder Shared-Hosting-Fallback)

---

## 15. Offene Entscheidungen

| Frage | Optionen |
|---|---|
| TypeScript? | Ja (strenger, bessere DX mit shadcn/ui) vs. Nein (schnellerer Start) |
| Projektname | TBD |
| Hosting | VPS (für SSR) vs. Shared Hosting (ohne SSR) |
| shadcn/ui Theme | Neutral (Default) vs. Custom Theme |

### Entschieden ✅

| Entscheidung | Wahl |
|---|---|
| UI Components | shadcn/ui (Radix + Tailwind) |
| Styling | Tailwind CSS 4 |
| Code-Highlighting | Shiki (serverseitig, zero JS) |
| WYSIWYG Editor | Lexical (Facebook) – Rich-Text mit Markdown-Shortcuts |
| Icons | Lucide React (shadcn/ui Standard) |
