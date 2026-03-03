<footer class="border-t border-neutral-200 dark:border-neutral-800">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-neutral-500 dark:text-neutral-500">
        <p>&copy; {{ date('Y') }} Kopfsalat</p>
        <div class="flex gap-4">
            <a href="{{ route('page.show', 'impressum') }}" class="hover:text-neutral-900 dark:hover:text-neutral-100">Impressum</a>
            <a href="{{ route('page.show', 'datenschutz') }}" class="hover:text-neutral-900 dark:hover:text-neutral-100">Datenschutz</a>
        </div>
    </div>
</footer>
