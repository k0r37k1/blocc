@props([
    'label',
])

<span @class([
    'inline-flex items-center gap-1.5 rounded-full border border-neutral-200 bg-neutral-50 px-2.5 py-1 text-xs font-medium text-neutral-700',
    'dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200',
])>
    <span>{{ $label }}</span>
    <button
        type="button"
        {{ $attributes->class([
            'rounded-full p-0.5 text-neutral-400 transition-colors hover:text-neutral-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:text-neutral-500 dark:hover:text-neutral-200',
        ]) }}
        aria-label="{{ __('Remove filter') }}"
    >
        <x-heroicon-o-x-mark class="h-3 w-3 shrink-0" aria-hidden="true" />
    </button>
</span>
