@props(['post', 'index' => 0])

<article
    x-data
    x-intersect.once="$el.classList.add('revealed')"
    class="reveal post-card-hover group -mx-4 rounded-lg px-4 py-6 hover:bg-neutral-50 dark:hover:bg-neutral-900"
    style="transition-delay: {{ $index * 80 }}ms"
>
    @if ($post->getFirstMediaUrl('featured-image', 'thumbnail'))
        <a href="{{ route('blog.show', $post) }}" class="block">
            <img
                src="{{ $post->getFirstMediaUrl('featured-image', 'medium') }}"
                alt="{{ $post->featured_image_alt ?? $post->title }}"
                class="w-full aspect-[2/1] object-cover rounded-lg bg-neutral-100 dark:bg-neutral-800"
                loading="lazy"
                width="800"
                height="400"
            >
        </a>
    @endif

    <div class="@if ($post->getFirstMediaUrl('featured-image', 'thumbnail')) mt-4 @endif">
        <div class="flex items-center gap-2 text-sm text-muted dark:text-muted-dark">
            <time datetime="{{ $post->published_at->toDateString() }}">
                {{ $post->published_at->translatedFormat('j. F Y') }}
            </time>

            @if ($post->reading_time)
                <span>&middot;</span>
                <span>{{ __(':count min', ['count' => $post->reading_time]) }}</span>
            @endif

            @if ($post->category)
                <span>&middot;</span>
                <a
                    href="{{ route('category.show', $post->category) }}"
                    class="font-medium"
                    style="color: {{ $post->category->color }}"
                >
                    {{ $post->category->name }}
                </a>
            @endif
        </div>

        <h2 class="mt-1.5 text-xl font-bold tracking-tight">
            <a href="{{ route('blog.show', $post) }}" class="text-neutral-900 dark:text-neutral-100 hover:text-accent dark:hover:text-accent transition-colors">
                {{ $post->title }}
            </a>
        </h2>

        @if ($post->excerpt)
            <p class="mt-2 text-neutral-600 dark:text-neutral-400 leading-relaxed line-clamp-2">
                {{ $post->excerpt }}
            </p>
        @endif
    </div>
</article>
