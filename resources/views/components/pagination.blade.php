@php
    $previousLabel = $previousLabel ?? __('Newer posts');
    $nextLabel = $nextLabel ?? __('Older posts');
    $wirePagination = $wirePagination ?? false;
    $pageName = $paginator->getPageName();
@endphp

@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="{{ __('Page navigation') }}"
        @if ($wirePagination)
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="previousPage,nextPage,gotoPage"
        @endif
        class="mt-8 border-t border-neutral-200 pt-8 transition-opacity duration-200 dark:border-neutral-800"
    >
        <div class="flex flex-wrap items-center justify-center gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span
                    aria-disabled="true"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md text-neutral-400 dark:text-neutral-500"
                >
                    <span class="sr-only">{{ $previousLabel }}</span>
                    <span aria-hidden="true">&larr;</span>
                </span>
            @elseif ($wirePagination)
                <button
                    type="button"
                    wire:click="previousPage('{{ $pageName }}')"
                    wire:loading.attr="disabled"
                    wire:target="previousPage,nextPage,gotoPage"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md text-neutral-700 hover:text-accent disabled:opacity-50 dark:text-neutral-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                    aria-label="{{ $previousLabel }}"
                >
                    <span aria-hidden="true">&larr;</span>
                </button>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md text-neutral-700 hover:text-accent dark:text-neutral-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                    aria-label="{{ $previousLabel }}"
                >
                    <span aria-hidden="true">&larr;</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex h-9 min-w-9 items-center justify-center px-2 text-sm text-neutral-400 dark:text-neutral-500">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span
                                aria-current="page"
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-md bg-neutral-900 px-3 text-sm font-medium text-white dark:bg-neutral-100 dark:text-neutral-900"
                            >
                                {{ $page }}
                            </span>
                        @elseif ($wirePagination)
                            <button
                                type="button"
                                wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                wire:loading.attr="disabled"
                                wire:target="previousPage,nextPage,gotoPage"
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900 disabled:opacity-50 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:hover:text-neutral-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                            >
                                {{ $page }}
                            </button>
                        @else
                            <a
                                href="{{ $url }}"
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:hover:text-neutral-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                            >
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                @if ($wirePagination)
                    <button
                        type="button"
                        wire:click="nextPage('{{ $pageName }}')"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-md text-neutral-700 hover:text-accent disabled:opacity-50 dark:text-neutral-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                        aria-label="{{ $nextLabel }}"
                    >
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                @else
                    <a
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-md text-neutral-700 hover:text-accent dark:text-neutral-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                        aria-label="{{ $nextLabel }}"
                    >
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                @endif
            @else
                <span
                    aria-disabled="true"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md text-neutral-400 dark:text-neutral-500"
                >
                    <span class="sr-only">{{ $nextLabel }}</span>
                    <span aria-hidden="true">&rarr;</span>
                </span>
            @endif
        </div>
    </nav>
@endif
