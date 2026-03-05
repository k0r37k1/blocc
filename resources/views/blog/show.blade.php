<x-layout
    :title="$post->title . ' - Kopfsalat'"
    :description="$post->excerpt"
    :og-title="$post->title"
    :og-description="$post->excerpt"
    :og-image="$post->getFirstMediaUrl('featured-image') ?: null"
    og-type="article"
>
    <x-slot:meta>
        <meta property="article:published_time" content="{{ $post->published_at->toW3cString() }}">
        <meta property="article:modified_time" content="{{ $post->updated_at->toW3cString() }}">
        @foreach ($post->tags as $tag)
            <meta property="article:tag" content="{{ $tag->name }}">
        @endforeach
    </x-slot:meta>

    <article>
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">
            {{ $post->title }}
        </h1>

        <div class="mt-3 flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-500">
            <time datetime="{{ $post->published_at->toDateString() }}">
                {{ $post->published_at->translatedFormat('j. F Y') }}
            </time>

            @if ($post->category)
                <span>&middot;</span>
                <a href="{{ route('category.show', $post->category) }}" style="color: {{ $post->category->color }}">
                    {{ $post->category->name }}
                </a>
            @endif

            <span>&middot;</span>
            <span>{{ $post->reading_time }} Min. Lesezeit</span>
        </div>

        @if ($post->getFirstMediaUrl('featured-image'))
            <figure class="mt-6">
                <img
                    src="{{ $post->getFirstMediaUrl('featured-image', 'medium') }}"
                    alt="{{ $post->featured_image_alt ?? $post->title }}"
                    class="w-full aspect-video object-cover rounded-lg"
                    loading="lazy"
                    width="800"
                    height="450"
                >
            </figure>
        @endif

        <div x-data="codeBlocks">
            <x-prose class="mt-8">
                {!! $post->body !!}
            </x-prose>
        </div>

        @if ($post->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <a
                        href="{{ route('tag.show', $tag) }}"
                        class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-accent dark:hover:text-accent"
                    >
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </article>

    @if ($previousPost || $nextPost)
        <nav class="mt-12 pt-8 border-t border-neutral-200 dark:border-neutral-800 flex items-start justify-between gap-4">
            <div>
                @if ($previousPost)
                    <a href="{{ route('blog.show', $previousPost->slug) }}" class="group">
                        <span class="text-xs text-neutral-500 dark:text-neutral-500">Vorheriger Beitrag</span>
                        <p class="text-accent group-hover:underline">&larr; {{ $previousPost->title }}</p>
                    </a>
                @endif
            </div>

            <div class="text-right">
                @if ($nextPost)
                    <a href="{{ route('blog.show', $nextPost->slug) }}" class="group">
                        <span class="text-xs text-neutral-500 dark:text-neutral-500">N&auml;chster Beitrag</span>
                        <p class="text-accent group-hover:underline">{{ $nextPost->title }} &rarr;</p>
                    </a>
                @endif
            </div>
        </nav>
    @endif
</x-layout>
