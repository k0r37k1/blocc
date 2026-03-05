@props([
    'title' => null,
    'description' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'canonicalUrl' => null,
])

@php
    $description ??= config('app.description');
    $resolvedOgTitle = $ogTitle ?? $title ?? config('app.name');
    $resolvedOgDescription = $ogDescription ?? $description;
    $resolvedOgImage = $ogImage ?? asset('images/og-default.png');
    $resolvedCanonicalUrl = $canonicalUrl ?? url()->current();
    $twitterCard = $ogImage ? 'summary_large_image' : 'summary';
@endphp

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name') }}</title>

        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ $resolvedCanonicalUrl }}">

        {{-- Open Graph --}}
        <meta property="og:title" content="{{ $resolvedOgTitle }}">
        <meta property="og:description" content="{{ $resolvedOgDescription }}">
        <meta property="og:url" content="{{ $resolvedCanonicalUrl }}">
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:image" content="{{ $resolvedOgImage }}">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="{{ $twitterCard }}">
        <meta name="twitter:title" content="{{ $resolvedOgTitle }}">
        <meta name="twitter:description" content="{{ $resolvedOgDescription }}">
        <meta name="twitter:image" content="{{ $resolvedOgImage }}">

        {{-- RSS auto-discovery --}}
        <link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }}" href="{{ url('/feed') }}">

        {{ $meta ?? '' }}

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
    </head>
    <body class="bg-white dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 font-sans antialiased transition-colors duration-200">
        <x-header />

        <main class="max-w-3xl mx-auto px-4 py-8 sm:px-6">
            {{ $slot }}
        </main>

        <x-footer />
    </body>
</html>
