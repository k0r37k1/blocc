<x-layout :title="__('Subscription confirmed')">
    <div class="text-center py-12">
        <div class="mb-4 flex justify-center">
            <span class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
        </div>

        <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100 mb-2">
            {{ __('Subscription confirmed!') }}
        </h1>

        <p class="text-muted dark:text-muted-dark mb-6">
            {{ __('You have successfully subscribed to the newsletter. Welcome!') }}
        </p>

        <a
            href="{{ route('blog.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-accent hover:underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm"
        >
            ← {{ __('Back to blog') }}
        </a>
    </div>
</x-layout>
