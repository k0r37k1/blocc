<x-layout title="Kopfsalat" :description="config('app.description')">
    <div class="space-y-2">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <p class="py-12 text-center text-neutral-500 dark:text-neutral-400">
                Noch keine Beitr&auml;ge vorhanden.
            </p>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
