@php
    $pageDescription = \Illuminate\Support\Str::limit(strip_tags($page->body_raw ?? $page->body), 160);
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $page->title,
        'description' => $pageDescription,
        'url' => url()->current(),
        'dateModified' => $page->updated_at->toW3cString(),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => \App\Models\Setting::get('blog_name', config('app.name')),
            'url' => url('/'),
        ],
    ];
@endphp
<x-layout
    :title="$page->title . ' - ' . config('app.name')"
    :description="$pageDescription"
    :edit-url="url('/admin/pages/' . $page->slug . '/edit')"
>
    <x-slot:meta>
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-slot:meta>

    <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">{{ $page->title }}</h1>

    <div x-data="codeBlocks" data-copy-label="{{ __('Copy') }}" data-copied-label="{{ __('Copied') }}">
        <x-prose class="mt-6">
            {!! $page->body !!}
        </x-prose>
    </div>
</x-layout>
