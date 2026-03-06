<button
    x-data="{ visible: false }"
    x-on:scroll.window="visible = window.scrollY > 400"
    x-show="visible"
    x-transition.opacity
    x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
    class="fixed bottom-4 right-4 z-50 rounded-full bg-neutral-900/80 p-2.5 text-white shadow-lg backdrop-blur-sm transition-colors hover:bg-neutral-900 dark:bg-neutral-100/80 dark:text-neutral-900 dark:hover:bg-neutral-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
    aria-label="{{ __('Scroll to top') }}"
>
    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
    </svg>
</button>
