<div>
    {{-- Filter dropdowns --}}
    @if ($this->availableYears->isNotEmpty())
        <div class="flex justify-end items-center gap-3 mb-8">
            @if (filled($year) || filled($month))
                <button
                    wire:click="$set('year', ''); $set('month', '')"
                    type="button"
                    class="text-neutral-400 dark:text-neutral-500 hover:text-red-500 dark:hover:text-red-400 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm"
                    aria-label="{{ __('Clear filter') }}"
                >
                    <x-heroicon-o-x-mark class="w-4 h-4" aria-hidden="true" />
                </button>
            @endif

            <select
                wire:model.live="year"
                class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-transparent text-sm px-3 py-2 text-neutral-900 dark:text-neutral-100 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
            >
                <option value="">{{ __('All years') }}</option>
                @foreach ($this->availableYears as $item)
                    <option value="{{ $item['year'] }}">{{ $item['year'] }} ({{ $item['count'] }})</option>
                @endforeach
            </select>

            @if (filled($year))
                <select
                    wire:model.live="month"
                    class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-transparent text-sm px-3 py-2 text-neutral-900 dark:text-neutral-100 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
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
                            <time datetime="{{ $post->published_at->toDateString() }}" class="text-sm text-neutral-500 dark:text-neutral-400 tabular-nums shrink-0">
                                {{ $post->published_at->translatedFormat('j. M') }}
                            </time>
                            <a href="{{ route('blog.show', $post) }}" class="text-neutral-900 dark:text-neutral-100 hover:text-accent dark:hover:text-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                                {{ $post->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="text-neutral-500 dark:text-neutral-400">{{ __('No posts yet.') }}</p>
        @endforelse
    </div>
</div>
