<div class="post-list-livewire">
    {{-- Search bar --}}
    <div class="flex justify-end mb-8">
        <div class="relative w-1/2 sm:w-1/3">
            <input
                wire:model.live.debounce.250ms="search"
                type="search"
                placeholder="{{ __('Search…') }}"
                class="w-full border border-neutral-200 dark:border-neutral-800 rounded-md bg-transparent text-sm pl-3 pr-9 py-2 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-600 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                aria-label="{{ __('Search posts') }}"
            >
            <button
                wire:click="$set('sort', '{{ $sort === 'newest' ? 'oldest' : 'newest' }}')"
                type="button"
                class="absolute inset-y-0 right-0 flex items-center px-2.5 text-neutral-400 dark:text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors focus-visible:outline-none"
                aria-label="{{ $sort === 'newest' ? __('Sorted: newest first') : __('Sorted: oldest first') }}"
                title="{{ $sort === 'newest' ? __('Newest first') : __('Oldest first') }}"
            >
                @if ($sort === 'newest')
                    <x-heroicon-o-bars-arrow-down class="w-4 h-4" aria-hidden="true" />
                @else
                    <x-heroicon-o-bars-arrow-up class="w-4 h-4" aria-hidden="true" />
                @endif
            </button>
        </div>
    </div>

    {{-- Post list --}}
    <div
        x-auto-animate
        class="divide-y divide-neutral-200 dark:divide-neutral-800"
    >
        @forelse ($this->posts as $post)
            <div wire:key="post-{{ $post->id }}" class="py-8 first:pt-0">
                <x-post-card :post="$post" :index="$loop->index" />
            </div>
        @empty
            <p wire:key="empty-state" class="py-12 text-center text-neutral-500 dark:text-neutral-400">
                {{ filled($this->search) ? __('No posts found.') : __('No posts yet.') }}
            </p>
        @endforelse
    </div>

    {{ $this->posts->links('components.pagination') }}

    @if (\App\Models\Setting::get('newsletter_enabled', '0') === '1')
        <div class="mt-16 rounded-lg px-4 py-5" style="background-color: var(--color-card)">
            <livewire:newsletter-subscribe variant="card" />
        </div>
    @endif
</div>
