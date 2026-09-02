<div>
    @if ($this->hasActiveFilters)
        <div class="mb-4 flex flex-wrap items-center gap-2">
            @if (filled($year))
                <x-active-filter
                    :label="__('Year: :year', ['year' => $year])"
                    wire:click="clearYear"
                />
            @endif

            @if ($monthLabel = $this->activeMonthLabel)
                <x-active-filter
                    :label="$monthLabel"
                    wire:click="clearMonth"
                />
            @endif
        </div>
    @endif

    {{-- Filter dropdowns --}}
    @if ($this->availableYears->isNotEmpty())
        <div class="mb-8 flex flex-wrap items-center justify-stretch gap-3 sm:justify-end">
            <select
                wire:model.live.preserve-scroll="year"
                class="w-full min-w-0 rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm text-neutral-900 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent sm:w-auto dark:border-neutral-800 dark:text-neutral-100 dark:[color-scheme:dark]"
            >
                <option value="">{{ __('All years') }}</option>
                @foreach ($this->availableYears as $item)
                    <option value="{{ $item['year'] }}">{{ $item['year'] }} ({{ $item['count'] }})</option>
                @endforeach
            </select>

            @if (filled($year))
                <select
                    wire:model.live.preserve-scroll="month"
                    class="w-full min-w-0 rounded-md border border-neutral-200 bg-transparent px-3 py-2 text-sm text-neutral-900 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent sm:w-auto dark:border-neutral-800 dark:text-neutral-100 dark:[color-scheme:dark]"
                >
                    <option value="">{{ __('All months') }}</option>
                    @foreach ($this->availableMonths as $item)
                        <option value="{{ $item['month'] }}">{{ $item['label'] }} ({{ $item['count'] }})</option>
                    @endforeach
                </select>
            @endif
        </div>
    @endif

    {{-- Post list --}}
    <div x-auto-animate>
        @forelse ($this->postsByYear as $yearKey => $posts)
            <section wire:key="year-{{ $yearKey }}" class="mt-8 first:mt-0">
                <h2 class="text-lg font-semibold text-neutral-700 dark:text-neutral-300">
                    {{ $yearKey }} <span class="text-sm font-normal text-neutral-500">({{ $posts->count() }})</span>
                </h2>

                <ul class="mt-3 space-y-2">
                    @foreach ($posts as $post)
                        <li wire:key="post-{{ $post->slug }}" class="flex items-baseline gap-3">
                            <time datetime="{{ $post->published_at->toDateString() }}" class="shrink-0 text-sm tabular-nums text-neutral-500 dark:text-neutral-400">
                                {{ $post->published_at->translatedFormat('j. M') }}
                            </time>
                            <a href="{{ route('blog.show', $post) }}" class="rounded-sm text-neutral-900 hover:text-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:text-neutral-100 dark:hover:text-accent">
                                {{ $post->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="text-neutral-500 dark:text-neutral-400">
                @if ($this->hasActiveFilters)
                    {{ __('No posts match your filters.') }}
                @else
                    {{ __('No posts yet.') }}
                @endif
            </p>
        @endforelse
    </div>
</div>
