<x-layout :title="'404 - ' . __('Page not found')" :description="__('Page not found')">
    @php
        $recentPosts = \App\Models\Post::published()
            ->latest('published_at')
            ->limit(3)
            ->get(['title', 'slug', 'published_at']);
    @endphp

    <div class="flex flex-col items-center justify-center text-center min-h-[50vh]">
        <p class="text-6xl font-bold text-neutral-300 dark:text-neutral-500 select-none" aria-hidden="true">
            404
        </p>

        <h1 class="mt-4 text-xl text-neutral-600 dark:text-neutral-400">
            {{ __('Page not found') }}
        </h1>

        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            {{ __('Nothing but lettuce here.') }}
        </p>

        <a href="{{ route('blog.index') }}" class="mt-6 text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
            {{ __('Go to homepage') }}
        </a>

        @if ($recentPosts->isNotEmpty())
            <div class="mt-12 w-full max-w-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-neutral-500 dark:text-neutral-400">
                    {{ __('Recent posts') }}
                </p>

                <ul class="mt-3 space-y-2">
                    @foreach ($recentPosts as $post)
                        <li>
                            <a href="{{ route('blog.show', $post) }}" class="text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                                {{ $post->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layout>
