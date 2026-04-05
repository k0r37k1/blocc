<div class="rounded-md border border-dashed border-neutral-300 bg-neutral-50 px-3 py-2 text-sm text-neutral-600 dark:border-neutral-600 dark:bg-neutral-900 dark:text-neutral-400">
    @if (filled($url))
        <span class="font-medium">{{ __('Video URL') }}:</span>
        <span class="break-all">{{ \Illuminate\Support\Str::limit($url, 96) }}</span>
    @else
        {{ __('Video URL') }}
    @endif
</div>
