<x-layout :title="config('app.name')" :description="config('app.description')">
    <div class="space-y-2">
        @forelse ($posts as $post)
            <x-post-card :post="$post" :index="$loop->index" />
        @empty
            <p class="py-12 text-center text-neutral-500 dark:text-neutral-400">
                {{ __('No posts yet.') }}
            </p>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
