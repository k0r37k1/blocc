<div
    x-data="{
        search: '',
        matches(name) {
            if (this.search.trim() === '') {
                return true;
            }

            return name.toLowerCase().includes(this.search.trim().toLowerCase());
        },
    }"
    class="w-full min-w-0 space-y-4"
>
    @if ($items->isEmpty())
        <x-filament::empty-state
            :heading="__('Empty')"
            :description="__('No images uploaded yet. Add JPEG, PNG, or WebP files under Media → Upload — they can be used for posts and site assets.')"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedPhoto"
            icon-color="gray"
        />
    @else
        <div class="w-full min-w-0">
            <label for="site-media-library-search" class="sr-only">{{ __('Search media') }}</label>
            <input
                id="site-media-library-search"
                type="search"
                x-model="search"
                placeholder="{{ __('Search by filename…') }}"
                class="fi-input block w-full min-w-0 rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10"
            />
        </div>

        <div class="grid max-h-[min(28rem,55dvh)] grid-cols-1 gap-3 overflow-y-auto overscroll-contain min-[480px]:grid-cols-2 md:max-h-[28rem] md:grid-cols-3">
            @foreach ($items as $item)
                @php
                    $label = app(\App\Services\SiteMedia::class)->displayLabel($item);
                    $previewUrl = $item->hasGeneratedConversion('thumbnail')
                        ? $item->getUrl('thumbnail')
                        : $item->getUrl();
                    $isSelected = (int) ($selectedMediaId ?? 0) === $item->id;
                @endphp

                <button
                    type="button"
                    wire:click="$set('data.site_media_id', {{ $item->id }})"
                    x-show="matches(@js($label))"
                    @class([
                        'group overflow-hidden rounded-xl border text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-600',
                        'border-primary-600 ring-2 ring-primary-600/20' => $isSelected,
                        'border-gray-200 hover:border-gray-300 dark:border-white/10 dark:hover:border-white/20' => ! $isSelected,
                    ])
                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                    aria-label="{{ __('Select :filename', ['filename' => $label]) }}"
                >
                    <div class="aspect-[16/10] overflow-hidden bg-gray-100 dark:bg-gray-800">
                        <img
                            src="{{ $previewUrl }}"
                            alt=""
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <div class="px-2 py-2">
                        <p class="truncate text-xs font-medium text-gray-950 dark:text-white" title="{{ $label }}">
                            {{ $label }}
                        </p>
                    </div>
                </button>
            @endforeach
        </div>

        @if (filled($selectedMediaId))
            <button
                type="button"
                wire:click="$set('data.site_media_id', null)"
                class="text-sm font-medium text-gray-600 underline underline-offset-2 hover:text-gray-950 dark:text-gray-400 dark:hover:text-white"
            >
                {{ __('Clear selection') }}
            </button>
        @endif
    @endif
</div>
