<x-layout :title="__('Category: :name', ['name' => $category->name]) . ' - ' . config('app.name')" :description="$category->description ?? __('All posts in category :name', ['name' => $category->name])" :robots="$posts->isEmpty() ? 'noindex, follow' : null">
    <header class="mb-10">
        <p class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted-dark">{{ __('Category') }}</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-neutral-900 sm:text-3xl dark:text-neutral-100">
            {{ $category->name }}
        </h1>

        @if ($category->description)
            <p class="mt-2 max-w-2xl text-base leading-relaxed text-neutral-600 dark:text-neutral-400">{{ $category->description }}</p>
        @endif

        <p class="mt-3 text-sm text-muted dark:text-muted-dark">
            {{ trans_choice('{0} No posts|{1} :count post|[2,*] :count posts', $posts->total(), ['count' => $posts->total()]) }}
        </p>

        <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="mt-3 inline-block text-sm text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
            {{ __('Filter on homepage') }} &rarr;
        </a>
    </header>

    <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
        @forelse($posts as $post)
            <div class="py-8 first:pt-0">
                <x-post-card :post="$post" :index="$loop->index" />
            </div>
        @empty
            <div class="py-12 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">{{ __('No posts in this category.') }}</p>
                <a href="{{ route('blog.index') }}" class="mt-2 inline-block text-sm text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Show all posts') }} &rarr;</a>
            </div>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
