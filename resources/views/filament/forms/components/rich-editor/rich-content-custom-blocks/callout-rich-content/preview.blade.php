<div class="rounded-md border border-dashed border-neutral-300 bg-neutral-50 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-900">
    <span class="font-medium capitalize text-neutral-700 dark:text-neutral-300">{{ $variant }}</span>
    @if (filled($title))
        <span class="text-neutral-500"> — {{ \Illuminate\Support\Str::limit($title, 64) }}</span>
    @endif
</div>
