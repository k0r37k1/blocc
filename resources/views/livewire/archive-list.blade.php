<div>
    {{-- Year pills --}}
    @if ($this->availableYears->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach ($this->availableYears as $item)
                <button
                    wire:click="selectYear('{{ $item['year'] }}')"
                    type="button"
                    class="text-sm px-3 py-1 rounded-full border transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent {{ $year === $item['year'] ? 'border-accent text-accent' : 'border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:border-accent hover:text-accent' }}"
                >
                    {{ $item['year'] }} <span class="opacity-60">({{ $item['count'] }})</span>
                </button>
            @endforeach
        </div>

        {{-- Month pills (only when a year is selected) --}}
        @if (filled($year) && $this->availableMonths->isNotEmpty())
            <div x-auto-animate class="flex flex-wrap gap-2 mb-8">
                @foreach ($this->availableMonths as $item)
                    <button
                        wire:click="selectMonth('{{ $item['month'] }}')"
                        type="button"
                        class="text-sm px-3 py-1 rounded-full border transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent {{ $month === $item['month'] ? 'border-accent text-accent' : 'border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-400 hover:border-accent hover:text-accent' }}"
                    >
                        {{ $item['label'] }} <span class="opacity-60">({{ $item['count'] }})</span>
                    </button>
                @endforeach
            </div>
        @else
            <div class="mb-8"></div>
        @endif
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
