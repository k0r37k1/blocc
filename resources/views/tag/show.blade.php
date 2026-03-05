<x-layout :title="'Tag: ' . $tag->name . ' - Kopfsalat'" :description="'Alle Beitraege mit dem Tag ' . $tag->name">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Tag: {{ $tag->name }}</h1>

    <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
        @forelse($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <p class="py-12 text-center text-neutral-500 dark:text-neutral-500">Keine Beitraege mit diesem Tag vorhanden.</p>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
