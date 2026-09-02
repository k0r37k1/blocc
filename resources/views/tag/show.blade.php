<x-layout :title="__('Tag: :name', ['name' => $tag->name]) . ' - ' . config('app.name')" :description="__('All posts tagged :name', ['name' => $tag->name])" :robots="$posts->isEmpty() ? 'noindex, follow' : null">
    <header class="mb-10">
        <p class="text-sm font-semibold uppercase tracking-wide text-muted dark:text-muted-dark">{{ __('Tag') }}</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-neutral-900 sm:text-3xl dark:text-neutral-100">
            #{{ $tag->name }}
        </h1>
        <p class="mt-3 text-sm text-muted dark:text-muted-dark">
            {{ trans_choice('{0} No posts|{1} :count post|[2,*] :count posts', $posts->total(), ['count' => $posts->total()]) }}
        </p>

        <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="mt-3 inline-block text-sm text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
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
                <p class="text-neutral-500 dark:text-neutral-400">{{ __('No posts with this tag.') }}</p>
                <a href="{{ route('blog.index') }}" class="mt-2 inline-block text-sm text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Show all posts') }} &rarr;</a>
            </div>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}
</x-layout>
