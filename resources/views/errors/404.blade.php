<x-layout title="404 - Seite nicht gefunden">
    @php
        $recentPosts = \App\Models\Post::published()
            ->latest('published_at')
            ->limit(3)
            ->get(['title', 'slug', 'published_at']);
    @endphp

    <div class="flex flex-col items-center justify-center text-center" style="min-height: 50vh;">
        <p class="text-6xl font-bold text-neutral-300 dark:text-neutral-700 select-none">
            404
        </p>

        <h1 class="mt-4 text-xl text-neutral-600 dark:text-neutral-400">
            Seite nicht gefunden
        </h1>

        <p class="mt-2 text-sm text-neutral-500">
            Hier ist nur Salat.
        </p>

        <a href="{{ url('/') }}" class="mt-6 text-accent hover:underline">
            Zur Startseite
        </a>

        @if ($recentPosts->isNotEmpty())
            <div class="mt-12 w-full max-w-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-neutral-400 dark:text-neutral-600">
                    Aktuelle Beitraege
                </p>

                <ul class="mt-3 space-y-2">
                    @foreach ($recentPosts as $post)
                        <li>
                            <a href="{{ route('blog.show', $post) }}" class="text-accent hover:underline">
                                {{ $post->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layout>
