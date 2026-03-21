@props(['post', 'index' => 0])

@php
    $commentsEnabled = \App\Models\Setting::get('comments_enabled', '1') === '1' && $post->comments_enabled;
    $commentCount = $post->approved_comments_count ?? $post->approvedComments()->count();
    $hasImage = $post->getFirstMediaUrl('featured-image', 'thumbnail');
@endphp

<article
    x-data
    x-intersect.once="$el.classList.add('revealed')"
    class="reveal group py-4"
    style="transition-delay: {{ $index * 80 }}ms"
>
    @if ($hasImage)
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

    <div class="{{ $hasImage ? 'mt-4' : '-mx-4 px-4 py-4 rounded-lg group-hover:bg-[var(--color-card-hover)]' }}">
        <div class="flex items-center gap-2 text-sm text-muted dark:text-muted-dark">
            <time datetime="{{ $post->published_at->toDateString() }}">
                <span class="sm:hidden">{{ $post->published_at->format('d.m.Y') }}</span>
                <span class="hidden sm:inline">{{ $post->published_at->translatedFormat('j. F Y') }}</span>
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

            @if ($commentsEnabled)
                <span>&middot;</span>
                <a href="{{ route('blog.show', $post) }}#comments" class="hover:text-accent transition-colors">
                    {{ $commentCount > 0 ? trans_choice('{1} 1 comment|[2,*] :count comments', $commentCount, ['count' => $commentCount]) : __(':count comments', ['count' => 0]) }}
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
