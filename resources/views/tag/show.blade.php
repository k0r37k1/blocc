<x-layout :title="'Tag: ' . $tag->name . ' - Kopfsalat'" :description="'Alle Beitraege mit dem Tag ' . $tag->name">
    <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">Tag: {{ $tag->name }}</h1>

    <div class="space-y-2">
        @forelse($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="py-12 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">Keine Beitr&auml;ge mit diesem Tag vorhanden.</p>
                <a href="{{ route('blog.index') }}" class="mt-2 inline-block text-sm text-accent hover:underline">Alle Beitr&auml;ge anzeigen &rarr;</a>
            </div>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
