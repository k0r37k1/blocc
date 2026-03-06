<x-layout :title="__('Archive') . ' - ' . config('app.name')" :description="__('Archive')">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ __('Archive') }}</h1>

    @forelse($postsByYear as $year => $posts)
        <section class="mt-8">
            <h2 class="text-lg font-semibold text-neutral-700 dark:text-neutral-300">{{ $year }} <span class="text-sm font-normal text-neutral-500">({{ $posts->count() }})</span></h2>

            <ul class="mt-3 space-y-2">
                @foreach($posts as $post)
                    <li class="flex items-baseline gap-3">
                        <time datetime="{{ $post->published_at->toDateString() }}" class="text-sm text-neutral-500 dark:text-neutral-400 tabular-nums shrink-0">
                            {{ $post->published_at->translatedFormat('j. M') }}
                        </time>
                        <a href="{{ route('blog.show', $post) }}" class="text-neutral-900 dark:text-neutral-100 hover:text-accent dark:hover:text-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                            {{ $post->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="mt-8 text-neutral-500 dark:text-neutral-400">{{ __('No posts yet.') }}</p>
    @endforelse
</x-layout>
