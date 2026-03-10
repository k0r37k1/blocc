@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Page navigation') }}" class="flex items-center justify-between mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-800">
        <div>
            @if ($paginator->previousPageUrl())
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                    &larr; {{ __('Newer posts') }}
                </a>
            @else
                <span class="text-neutral-400 dark:text-neutral-500">
                    &larr; {{ __('Newer posts') }}
                </span>
            @endif
        </div>

        <div>
            @if ($paginator->nextPageUrl())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="text-accent hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                    {{ __('Older posts') }} &rarr;
                </a>
            @else
                <span class="text-neutral-400 dark:text-neutral-500">
                    {{ __('Older posts') }} &rarr;
                </span>
            @endif
        </div>
    </nav>
@endif
