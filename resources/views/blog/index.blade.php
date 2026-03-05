<x-layout title="Kopfsalat" :description="config('app.description')">
    <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <p class="py-12 text-center text-neutral-500 dark:text-neutral-500">
                Noch keine Beitr&auml;ge vorhanden.
            </p>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
