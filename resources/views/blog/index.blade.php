<x-layout :title="config('app.name')" :description="config('app.description')">
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
@endphp
    <div class="mb-10">
        <h1 class="{{ $heroTitleClass }} font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ \App\Models\Setting::get('blog_name', config('app.name')) }}
        </h1>
        @if ($description = \App\Models\Setting::get('blog_description'))
            <p class="mt-1 {{ $heroSubtitleClass }} text-muted dark:text-muted-dark">{{ $description }}</p>
        @endif
    </div>

    <livewire:post-list />
</x-layout>
