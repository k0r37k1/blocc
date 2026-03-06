<x-layout :title="__('Category: :name', ['name' => $category->name]) . ' - ' . config('app.name')" :description="$category->description ?? __('All posts in category :name', ['name' => $category->name])">
    <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">{{ __('Category: :name', ['name' => $category->name]) }}</h1>

    @if($category->description)
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">{{ $category->description }}</p>
    @endif

    <div class="space-y-2">
        @forelse($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="py-12 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">{{ __('No posts in this category.') }}</p>
                <a href="{{ route('blog.index') }}" class="mt-2 inline-block text-sm text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Show all posts') }} &rarr;</a>
            </div>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
