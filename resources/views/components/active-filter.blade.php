@props([
    'label',
])

<span @class([
    'inline-flex items-center gap-1 rounded-md border border-neutral-200 bg-transparent py-1 pl-2.5 pr-1 text-xs text-muted',
    'dark:border-neutral-800 dark:text-muted-dark',
])>
    <span>{{ $label }}</span>
    <button
        type="button"
        {{ $attributes->class([
            'rounded-sm p-0.5 text-neutral-400 transition-colors hover:text-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:text-neutral-500 dark:hover:text-accent',
        ]) }}
        aria-label="{{ __('Remove filter') }}"
    >
        <x-heroicon-o-x-mark class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
    </button>
</span>
