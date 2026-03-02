# Kopfsalat

## What This Is

A minimalist, content-focused dev blog at `kopfsalat.blog` — "Thoughts served fresh". Single-author blog with classic blog posts, a dev changelog, and static pages. Built with the TALL stack (Tailwind CSS, Alpine.js, Laravel 12, Livewire 3) and Filament 3 for the admin panel. Privacy-first, fast, no JavaScript framework overhead.

## Core Value

A clean, fast blog where writing and publishing content is frictionless — from editor to published post in seconds, with zero external dependencies or tracker bloat.

## Requirements

### Validated

(None yet — ship to validate)

### Active

- [ ] Single-user authentication (Filament built-in)
- [ ] Blog posts with WYSIWYG editor (filament-tiptap-editor), categories, tags, featured image
- [ ] Changelog entries (compact, date-centric, version-tagged, type-coded: added/changed/fixed/removed/security)
- [ ] Static pages (About, Impressum, Datenschutz)
- [ ] Draft/Published workflow with scheduled publishing
- [ ] SEO: meta tags, Open Graph, Twitter Cards, JSON-LD structured data, sitemap, canonical URLs
- [ ] RSS/Atom feeds (main, per category, changelog)
- [ ] Dark mode toggle (Alpine.js + Tailwind dark: variant)
- [ ] Media management via Spatie Media Library (image uploads, thumbnails, WebP conversions)
- [ ] Code highlighting via Shiki (server-side, zero client JS)
- [ ] SPA-like frontend navigation via Livewire wire:navigate
- [ ] Responsive, mobile-first design with self-hosted fonts (Inter + JetBrains Mono)
- [ ] DSGVO-compliant: no external trackers, no external fonts, no cookies beyond auth session
- [ ] Filament admin dashboard with stats widgets
- [ ] Auto-generated slugs from titles
- [ ] Reading time estimation
- [ ] Previous/Next post navigation
- [ ] Breadcrumb navigation

### Out of Scope

- Multi-user / roles / permissions — single author only
- Comments — deliberately no comment system
- Newsletter / email subscriptions — v2+ consideration
- Full-text search — v2+ via SQLite FTS5
- OAuth / social login — email/password only
- Image optimization pipeline (WebP/AVIF auto-conversion beyond Spatie basics) — v2+
- Multi-language / i18n — v2+
- API / headless mode — not needed

## Context

- Domain: `kopfsalat.blog` already secured
- Detailed technical spec exists at `docs/blog-spec-v1.4.md` covering data model, URL structure, Filament resource examples, Blade component architecture, and performance targets
- Blog serves as a personal dev blog with changelog character — mix of longer articles and short TIL/release-note style entries
- Three post types: `post` (classic blog), `changelog` (compact, versioned), `page` (static)
- SQLite as database — single-file, easy backup, sufficient for single-author blog
- Body content stored as HTML directly (from TipTap editor), optional Markdown export for RSS portability
- Shiki code highlighting applied server-side for zero client-side JS overhead
- Target: Lighthouse > 97, JS bundle < 30KB, TTFB < 100ms

## Constraints

- **Hosting**: Shared Hosting (PHP only, no Node.js, no Redis, no background processes beyond cron)
- **Stack**: TALL (Tailwind CSS 4 + Alpine.js + Laravel 12 + Livewire 3) + Filament 3
- **Database**: SQLite — no MySQL/PostgreSQL setup needed
- **Editor**: filament-tiptap-editor (awcodes) — extended TipTap with block editing, media embeds
- **Media**: Spatie Media Library — conversions, collections, responsive images
- **Auth**: Single-user only, Filament built-in auth
- **Privacy**: DSGVO-compliant, zero external requests (fonts self-hosted, no analytics, no CDN)
- **No JS framework**: No React, Vue, Inertia — Alpine.js only for minimal client interactivity

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| TALL Stack over React/Inertia | No Node.js needed on server, smaller JS footprint, shared hosting compatible, SEO native via Blade | — Pending |
| Filament 3 for admin | Complete admin panel out-of-the-box, no custom admin controllers needed | — Pending |
| filament-tiptap-editor over standard RichEditor | Block editing, drag & drop, media embeds, source editing — more powerful for dev blog content | — Pending |
| Spatie Media Library over custom MediaResource | Thumbnails, WebP conversions, responsive images, collections — proven package | — Pending |
| SQLite over MySQL | Single-file DB, trivial backup, sufficient for single-author blog, no server setup | — Pending |
| wire:navigate for frontend | SPA-like feel without full SPA complexity, smooth page transitions | — Pending |
| HTML storage (not Markdown-first) | TipTap outputs HTML directly, simpler pipeline, Markdown export only for RSS | — Pending |
| Shared Hosting | Cost-effective, PHP-only constraint shapes architecture positively (simpler) | — Pending |

---
*Last updated: 2026-03-02 after initialization*
