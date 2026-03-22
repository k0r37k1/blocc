<x-layout :title="__('Tag: :name', ['name' => $tag->name]) . ' - ' . config('app.name')" :description="__('All posts tagged :name', ['name' => $tag->name])">
    <h1 class="flex items-baseline gap-2 text-lg font-bold tracking-tight text-neutral-900 dark:text-neutral-100 mb-8">
        {{ __('Tag') }}
        <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">{{ $tag->name }}</span>
    </h1>

    <div class="space-y-2">
        @forelse($posts as $post)
            <x-post-card :post="$post" :index="$loop->index" />
        @empty
            <div class="py-12 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">{{ __('No posts with this tag.') }}</p>
                <a href="{{ route('blog.index') }}" class="mt-2 inline-block text-sm text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Show all posts') }} &rarr;</a>
            </div>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
