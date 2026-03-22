@props([
    'title' => null,
    'description' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'canonicalUrl' => null,
    'editUrl' => null,
])

@php
    $description ??= config('app.description');
    $resolvedOgTitle = $ogTitle ?? $title ?? config('app.name');
    $resolvedOgDescription = $ogDescription ?? $description;
    $resolvedOgImage = $ogImage;
    $resolvedCanonicalUrl = $canonicalUrl ?? url()->current();
    $twitterCard = $ogImage ? 'summary_large_image' : 'summary';

    $accentColor = \App\Models\Setting::get('accent_color', '#15803d');
    $accentColorDark = \App\Models\Setting::get('accent_color_dark', '#4ade80');
    $headingFontKey = \App\Models\Setting::get('heading_font', 'Inter');
    $bodyFontKey = \App\Models\Setting::get('body_font', 'Inter');
    $fontStacks = config('appearance.fonts');
    $headingFontStack = $fontStacks[$headingFontKey] ?? $fontStacks['Inter'];
    $bodyFontStack = $fontStacks[$bodyFontKey] ?? $fontStacks['Inter'];
    $siteAssets = \App\Models\Site::instance()->getMedia('uploads')->keyBy(fn ($m) => pathinfo($m->file_name, PATHINFO_FILENAME));
    $favicon = $siteAssets->get('favicon')?->getUrl();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name') }}</title>

        <meta name="description" content="{{ $description }}">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
        <link rel="canonical" href="{{ $resolvedCanonicalUrl }}">

        {{-- Open Graph --}}
        <meta property="og:site_name" content="{{ \App\Models\Setting::get('blog_name', config('app.name')) }}">
        <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale() === 'de' ? 'de_DE' : 'en_US') }}">
        <meta property="og:title" content="{{ $resolvedOgTitle }}">
        <meta property="og:description" content="{{ $resolvedOgDescription }}">
        <meta property="og:url" content="{{ $resolvedCanonicalUrl }}">
        <meta property="og:type" content="{{ $ogType }}">
        @if ($resolvedOgImage)
            <meta property="og:image" content="{{ $resolvedOgImage }}">
            <meta property="og:image:alt" content="{{ $resolvedOgTitle }}">
        @endif

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="{{ $twitterCard }}">
        <meta name="twitter:title" content="{{ $resolvedOgTitle }}">
        <meta name="twitter:description" content="{{ $resolvedOgDescription }}">
        @if ($resolvedOgImage)
            <meta name="twitter:image" content="{{ $resolvedOgImage }}">
            <meta name="twitter:image:alt" content="{{ $resolvedOgTitle }}">
        @endif

        {{-- Favicon --}}
        @if ($favicon)
            <link rel="icon" href="{{ $favicon }}">
            <link rel="apple-touch-icon" href="{{ $favicon }}">
        @endif

        {{-- RSS auto-discovery --}}
        <link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }}" href="{{ url('/feed') }}">

        {{ $meta ?? '' }}

        @if ($headScripts = \App\Models\Setting::get('head_scripts'))
            {!! $headScripts !!}
        @endif

        {{-- Self-hosted fonts --}}
        <link rel="preload" href="/fonts/inter-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="stylesheet" href="/css/fonts.css">

        {{-- FOUC prevention: must be blocking, before any CSS/JS render --}}
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Dynamic appearance overrides from settings --}}
        <style>
            :root {
                --color-accent: {{ $accentColor }};
                --font-sans: {!! $bodyFontStack !!};
                --font-heading: {!! $headingFontStack !!};
            }
            .dark {
                --color-accent: {{ $accentColorDark }};
            }
        </style>
    </head>
    <body class="text-neutral-900 dark:text-neutral-200 font-sans antialiased transition-colors duration-200" style="background-color: var(--color-bg)">
        {{-- Skip to content (WCAG 2.4.1) --}}
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:rounded-md focus:bg-accent-bg focus:px-4 focus:py-2 focus:text-white focus:outline-none">
            {{ __('Skip to content') }}
        </a>

        <x-header />

        <main id="main-content" class="max-w-3xl mx-auto px-4 py-10 sm:px-6 sm:py-12">
            {{ $slot }}
        </main>

        <x-footer />

        <x-back-to-top />
        <x-admin-bar :editUrl="$editUrl ?? null" />

        @livewireScripts
        @stack('scripts')
    </body>
</html>
