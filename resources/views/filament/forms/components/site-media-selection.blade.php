@if ($media)
    <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
        <img
            src="{{ $media->hasGeneratedConversion('thumbnail') ? $media->getUrl('thumbnail') : $media->getUrl() }}"
            alt=""
            class="h-20 w-32 shrink-0 rounded-lg object-cover"
        />
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                {{ app(\App\Services\SiteMedia::class)->displayLabel($media) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Selected from media') }}
            </p>
        </div>
        <button
            type="button"
            wire:click="$set('data.site_media_id', null)"
            class="shrink-0 text-sm font-medium text-gray-600 underline underline-offset-2 hover:text-gray-950 dark:text-gray-400 dark:hover:text-white"
        >
            {{ __('Remove') }}
        </button>
    </div>
@endif
