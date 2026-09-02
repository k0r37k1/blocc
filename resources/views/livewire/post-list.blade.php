<div class="post-list-livewire">
    {{-- Search bar --}}
    <div class="mb-6 flex justify-end">
        <div class="relative w-full sm:w-1/3">
            <input
                wire:model.live.debounce.250ms.preserve-scroll="search"
                type="search"
                placeholder="{{ __('Search…') }}"
                class="w-full rounded-md border border-neutral-200 bg-transparent py-2 pl-3 pr-9 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:border-neutral-800 dark:text-neutral-100 dark:placeholder-neutral-600"
                aria-label="{{ __('Search posts') }}"
                aria-busy="false"
                wire:loading.attr="aria-busy"
                wire:target="search,sort,category,tag,gotoPage,previousPage,nextPage,clearSearch,clearCategory,clearTag,clearFilters,selectCategory,toggleTag"
            >
            <button
                wire:click.preserve-scroll="$set('sort', '{{ $sort === 'newest' ? 'oldest' : 'newest' }}')"
                type="button"
                class="absolute inset-y-0 right-0 flex items-center px-2.5 text-neutral-400 transition-colors hover:text-neutral-700 focus-visible:outline-none dark:text-neutral-500 dark:hover:text-neutral-300"
                aria-label="{{ $sort === 'newest' ? __('Sorted: newest first') : __('Sorted: oldest first') }}"
                title="{{ $sort === 'newest' ? __('Newest first') : __('Oldest first') }}"
            >
                @if ($sort === 'newest')
                    <x-heroicon-o-bars-arrow-down class="h-4 w-4" aria-hidden="true" />
                @else
                    <x-heroicon-o-bars-arrow-up class="h-4 w-4" aria-hidden="true" />
                @endif
            </button>
        </div>
    </div>

    @if ($this->hasActiveFilters)
        <div class="mb-6 flex flex-wrap items-center gap-2">
            @if (filled(trim($search)))
                <x-active-filter
                    :label="__('Search: :term', ['term' => $search])"
                    wire:click="clearSearch"
                />
            @endif

            @if ($activeCategory = $this->activeCategory)
                <x-active-filter
                    :label="__('Category: :name', ['name' => $activeCategory->name])"
                    wire:click="clearCategory"
                />
            @elseif (filled($category))
                <x-active-filter
                    :label="__('Category: :name', ['name' => $category])"
                    wire:click="clearCategory"
                />
            @endif

            @if ($activeTag = $this->activeTag)
                <x-active-filter
                    :label="__('Tag: :name', ['name' => $activeTag->name])"
                    wire:click="clearTag"
                />
            @elseif (filled($tag))
                <x-active-filter
                    :label="__('Tag: :name', ['name' => $tag])"
                    wire:click="clearTag"
                />
            @endif

            @if (collect([filled(trim($search)), filled($category), filled($tag)])->filter()->count() > 1)
                <button
                    type="button"
                    wire:click.preserve-scroll="clearFilters"
                    class="text-xs font-medium text-muted transition-colors hover:text-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:text-muted-dark"
                >
                    {{ __('Clear all filters') }}
                </button>
            @endif
        </div>
    @endif

    @if ($this->showFilterChips)
        <div class="mb-8 space-y-4">
            @if ($this->categories->isNotEmpty())
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted-dark">{{ __('Categories') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->categories as $categoryOption)
                            <button
                                type="button"
                                wire:key="category-chip-{{ $categoryOption->slug }}"
                                wire:click.preserve-scroll="selectCategory('{{ $categoryOption->slug }}')"
                                @class([
                                    'rounded-full border px-3 py-1 text-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                                    'border-accent bg-accent text-white' => $category === $categoryOption->slug,
                                    'border-neutral-200 bg-transparent text-neutral-700 hover:border-neutral-300 hover:text-accent dark:border-neutral-700 dark:text-neutral-300 dark:hover:border-neutral-600 dark:hover:text-accent' => $category !== $categoryOption->slug,
                                ])
                                aria-pressed="{{ $category === $categoryOption->slug ? 'true' : 'false' }}"
                            >
                                {{ $categoryOption->name }}
                                <span class="text-xs opacity-75">({{ $categoryOption->posts_count }})</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($this->tags->isNotEmpty())
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted dark:text-muted-dark">{{ __('Tags') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->tags as $tagOption)
                            <button
                                type="button"
                                wire:key="tag-chip-{{ $tagOption->slug }}"
                                wire:click.preserve-scroll="toggleTag('{{ $tagOption->slug }}')"
                                @class([
                                    'rounded-full border px-3 py-1 text-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                                    'border-accent bg-accent text-white' => $tag === $tagOption->slug,
                                    'border-neutral-200 bg-transparent text-neutral-700 hover:border-neutral-300 hover:text-accent dark:border-neutral-700 dark:text-neutral-300 dark:hover:border-neutral-600 dark:hover:text-accent' => $tag !== $tagOption->slug,
                                ])
                                aria-pressed="{{ $tag === $tagOption->slug ? 'true' : 'false' }}"
                            >
                                #{{ $tagOption->name }}
                                <span class="text-xs opacity-75">({{ $tagOption->posts_count }})</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <p
        wire:loading
        wire:target="search,sort,category,tag,gotoPage,previousPage,nextPage,clearSearch,clearCategory,clearTag,clearFilters,selectCategory,toggleTag"
        class="mb-4 text-sm text-muted dark:text-muted-dark"
        aria-live="polite"
    >
        {{ __('Loading…') }}
    </p>

    {{-- Post list --}}
    <div
        x-auto-animate
        wire:loading.class="opacity-60 pointer-events-none"
        wire:target="search,sort,category,tag,gotoPage,previousPage,nextPage,clearSearch,clearCategory,clearTag,clearFilters,selectCategory,toggleTag"
        class="divide-y divide-neutral-200 transition-opacity duration-200 dark:divide-neutral-800"
    >
        @forelse ($this->posts as $post)
            <div wire:key="post-{{ $post->id }}" class="py-8 first:pt-0">
                <x-post-card :post="$post" :index="$loop->index" />
            </div>
        @empty
            <p wire:key="empty-state" class="py-12 text-center text-neutral-500 dark:text-neutral-400">
                @if ($this->hasActiveFilters)
                    {{ __('No posts match your filters.') }}
                @elseif (filled($this->search))
                    {{ __('No posts found.') }}
                @else
                    {{ __('No posts yet.') }}
                @endif
            </p>
        @endforelse
    </div>

    {{ $this->posts->links('components.pagination', ['wirePagination' => true]) }}
</div>
