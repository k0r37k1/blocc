<x-layout :title="'404 - ' . __('Page not found')" :description="__('Page not found')">
    <x-slot:meta>
        <meta name="robots" content="noindex, follow">
    </x-slot:meta>

    <div class="flex min-h-[45vh] flex-col items-center justify-center text-center px-4">
        <p class="select-none text-6xl font-bold tracking-tight text-neutral-500 dark:text-neutral-400" aria-hidden="true">
            404
        </p>

        <h1 class="mt-4 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ __('Page not found') }}
        </h1>

        <p class="mt-2 max-w-prose text-sm leading-relaxed text-neutral-600 dark:text-neutral-400">
            {{ __('Nothing but lettuce here.') }}
        </p>

        <div class="mt-6 w-full flex items-center justify-center">
            <a
                href="{{ route('blog.index') }}"
                class="inline-flex items-center justify-center rounded-sm border border-neutral-200 dark:border-neutral-800 px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-100 hover:text-accent hover:border-accent/60 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
            >
                {{ __('Go to homepage') }}
            </a>
        </div>

    </div>
</x-layout>
