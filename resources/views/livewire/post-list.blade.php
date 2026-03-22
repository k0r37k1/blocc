<div class="post-list-livewire">
    {{-- Filter bar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center mb-6">
        <div class="relative flex-1">
            <input
                wire:model.live.debounce.250ms="search"
                type="search"
                placeholder="{{ __('Search posts…') }}"
                class="w-full border border-neutral-200 dark:border-neutral-800 rounded-md bg-transparent text-sm px-3 py-2 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-600 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                aria-label="{{ __('Search posts') }}"
            >
        </div>

        @if ($this->categories->isNotEmpty())
            <select
                wire:model.live="category"
                class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-transparent text-sm px-3 py-2 text-neutral-600 dark:text-neutral-400 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                aria-label="{{ __('Filter by category') }}"
            >
                <option value="">{{ __('All categories') }}</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->name }} ({{ $cat->posts_count }})</option>
                @endforeach
            </select>
        @endif

        <select
            wire:model.live="sort"
            class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-transparent text-sm px-3 py-2 text-neutral-600 dark:text-neutral-400 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
            aria-label="{{ __('Sort posts') }}"
        >
            <option value="newest">{{ __('Newest first') }}</option>
            <option value="oldest">{{ __('Oldest first') }}</option>
        </select>

        @if ($this->hasActiveFilters())
            <button
                wire:click="clearFilters"
                type="button"
                class="text-sm text-neutral-500 dark:text-neutral-400 hover:text-accent dark:hover:text-accent transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm whitespace-nowrap"
            >
                {{ __('Clear filters') }}
            </button>
        @endif
    </div>

    {{-- Tag pills --}}
    @if ($this->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-8">
            @foreach ($this->tags as $t)
                <button
                    wire:click="toggleTag('{{ $t->slug }}')"
                    type="button"
                    class="text-sm px-3 py-1 rounded-full border transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent {{ $tag === $t->slug ? 'border-accent text-accent dark:border-accent dark:text-accent' : 'border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:border-accent hover:text-accent dark:hover:border-accent dark:hover:text-accent' }}"
                    aria-pressed="{{ $tag === $t->slug ? 'true' : 'false' }}"
                >
                    {{ $t->name }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Post list --}}
    <div
        x-auto-animate
        class="divide-y divide-neutral-200 dark:divide-neutral-800"
    >
        @forelse ($this->posts as $post)
            <div wire:key="post-{{ $post->id }}" class="py-8 first:pt-0">
                <x-post-card :post="$post" />
            </div>
        @empty
            <p wire:key="empty-state" class="py-12 text-center text-neutral-500 dark:text-neutral-400">
                {{ $this->hasActiveFilters() ? __('No posts found for your filters.') : __('No posts yet.') }}
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
