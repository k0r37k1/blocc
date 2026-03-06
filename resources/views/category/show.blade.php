<x-layout :title="'Kategorie: ' . $category->name . ' - Kopfsalat'" :description="$category->description ?? 'Alle Beitraege in der Kategorie ' . $category->name">
    <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">Kategorie: {{ $category->name }}</h1>

    @if($category->description)
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">{{ $category->description }}</p>
    @endif

    <div class="space-y-2">
        @forelse($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="py-12 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">Keine Beitr&auml;ge in dieser Kategorie vorhanden.</p>
                <a href="{{ route('blog.index') }}" class="mt-2 inline-block text-sm text-accent hover:underline">Alle Beitr&auml;ge anzeigen &rarr;</a>
            </div>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
