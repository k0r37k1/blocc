<header class="border-b border-neutral-200 dark:border-neutral-800">
    <nav x-data="{ open: false }" class="max-w-3xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            {{-- Brand --}}
            <a href="{{ url('/') }}" class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                &#129388; Kopfsalat
            </a>

            {{-- Desktop nav --}}
            <div class="hidden sm:flex items-center gap-6">
                <a href="{{ url('/') }}" class="text-neutral-600 hover:text-accent dark:text-neutral-400 dark:hover:text-accent">Blog</a>
                <a href="#" class="text-neutral-600 hover:text-accent dark:text-neutral-400 dark:hover:text-accent">Ueber mich</a>
                <a href="#" class="text-neutral-600 hover:text-accent dark:text-neutral-400 dark:hover:text-accent">Archiv</a>

                {{-- Dark mode toggle --}}
                <button
                    x-data="{ dark: document.documentElement.classList.contains('dark') }"
                    x-on:click="
                        dark = !dark;
                        document.documentElement.classList.toggle('dark', dark);
                        localStorage.theme = dark ? 'dark' : 'light';
                    "
                    class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100"
                    aria-label="Toggle dark mode"
                >
                    {{-- Sun icon (shown in dark mode) --}}
                    <svg x-show="dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{-- Moon icon (shown in light mode) --}}
                    <svg x-show="!dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>

            {{-- Mobile hamburger button --}}
            <div class="flex items-center sm:hidden gap-2">
                {{-- Mobile dark mode toggle --}}
                <button
                    x-data="{ dark: document.documentElement.classList.contains('dark') }"
                    x-on:click="
                        dark = !dark;
                        document.documentElement.classList.toggle('dark', dark);
                        localStorage.theme = dark ? 'dark' : 'light';
                    "
                    class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100"
                    aria-label="Toggle dark mode"
                >
                    <svg x-show="dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="!dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                {{-- Hamburger button --}}
                <button x-on:click="open = !open" class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100" aria-label="Menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-transition class="sm:hidden pb-4">
            <a href="{{ url('/') }}" class="block py-2 text-neutral-600 hover:text-accent dark:text-neutral-400 dark:hover:text-accent">Blog</a>
            <a href="#" class="block py-2 text-neutral-600 hover:text-accent dark:text-neutral-400 dark:hover:text-accent">Ueber mich</a>
            <a href="#" class="block py-2 text-neutral-600 hover:text-accent dark:text-neutral-400 dark:hover:text-accent">Archiv</a>
        </div>
    </nav>
</header>
