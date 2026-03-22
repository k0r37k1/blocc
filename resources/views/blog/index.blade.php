@php
    $heroTitleClass = match(\App\Models\Setting::get('hero_title_size', 'L')) {
        'S' => 'text-2xl',
        'M' => 'text-3xl',
        'XL' => 'text-5xl',
        default => 'text-4xl',
    };
    $heroSubtitleClass = match(\App\Models\Setting::get('hero_subtitle_size', 'M')) {
        'S' => 'text-sm',
        'L' => 'text-lg',
        'XL' => 'text-xl',
        default => 'text-base',
    };
    $blogName = \App\Models\Setting::get('blog_name', config('app.name'));
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $blogName,
        'description' => \App\Models\Setting::get('blog_description', config('app.description', '')),
        'url' => url('/'),
    ];
@endphp
<x-layout :title="config('app.name')" :description="config('app.description')">
    <x-slot:meta>
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-slot:meta>

    <div class="mb-10">
        <h1 class="{{ $heroTitleClass }} font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ \App\Models\Setting::get('hero_title') ?: $blogName }}
        </h1>
        @if ($description = \App\Models\Setting::get('blog_description'))
            <p class="mt-1 {{ $heroSubtitleClass }} text-muted dark:text-muted-dark">{{ $description }}</p>
        @endif
    </div>

    <livewire:post-list />
</x-layout>
