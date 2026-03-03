@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Seitennavigation" class="flex items-center justify-between mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-800">
        <div>
            @if ($paginator->previousPageUrl())
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="text-accent hover:underline">
                    &larr; Neuere Beitr&auml;ge
                </a>
            @else
                <span class="text-neutral-400 dark:text-neutral-600">
                    &larr; Neuere Beitr&auml;ge
                </span>
            @endif
        </div>

        <div>
            @if ($paginator->nextPageUrl())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="text-accent hover:underline">
                    &Auml;ltere Beitr&auml;ge &rarr;
                </a>
            @else
                <span class="text-neutral-400 dark:text-neutral-600">
                    &Auml;ltere Beitr&auml;ge &rarr;
                </span>
            @endif
        </div>
    </nav>
@endif
