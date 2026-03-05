@props(['post'])

<article class="py-6">
    <time datetime="{{ $post->published_at->toDateString() }}" class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ $post->published_at->translatedFormat('j. F Y') }}
    </time>

    <h2 class="mt-1 text-xl font-semibold">
        <a href="{{ route('blog.show', $post) }}" class="text-neutral-900 dark:text-neutral-100 hover:text-accent dark:hover:text-accent">
            {{ $post->title }}
        </a>
    </h2>

    @if ($post->category)
        <a
            href="{{ route('category.show', $post->category) }}"
            class="mt-2 inline-block text-xs font-medium px-2 py-0.5 rounded-full"
            style="background-color: {{ $post->category->color }}20; color: {{ $post->category->color }}"
        >
            {{ $post->category->name }}
        </a>
    @endif

    @if ($post->excerpt)
        <p class="mt-2 text-neutral-600 dark:text-neutral-400 leading-relaxed">
            {{ $post->excerpt }}
        </p>
    @endif
</article>
