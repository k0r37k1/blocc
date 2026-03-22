@php
    $currentRoute = request()->route()?->getName();
    $currentLocale = app()->getLocale();
    $navPages = \App\Models\Page::query()->published()->where('show_in_nav', true)->orderBy('sort_order')->get();

    $navUrl = fn (\App\Models\Page $page): string => match ($page->slug) {
        '__blog' => route('blog.index'),
        '__archive' => route('archive'),
        default => route('page.show', $page->slug),
    };

    $isNavActive = fn (\App\Models\Page $page): bool => match ($page->slug) {
        '__blog' => $currentRoute === 'blog.index',
        '__archive' => $currentRoute === 'archive',
        default => $currentRoute === 'page.show' && request()->route('page') === $page->slug,
    };
@endphp

<header class="border-b border-neutral-100 dark:border-neutral-900">
    <nav x-data="{ open: false }" class="max-w-3xl mx-auto px-4 sm:px-6" aria-label="{{ __('Main navigation') }}">
        <div class="flex items-center h-16">
            {{-- Brand --}}
            <a href="{{ route('blog.index') }}" class="mr-auto text-lg font-bold text-neutral-900 dark:text-neutral-100 hover:text-accent dark:hover:text-accent transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                @php
                    $site = \App\Models\Site::instance();
                    $assets = $site->getMedia('uploads')->keyBy(fn ($m) => pathinfo($m->file_name, PATHINFO_FILENAME));
                    $logoLight = $assets->get('logo_light')?->getUrl();
                    $logoDark = $assets->get('logo_dark')?->getUrl() ?: $logoLight;
                    $blogName = \App\Models\Setting::get('blog_name', config('app.name'));
                @endphp
                @if ($logoLight)
                    <img src="{{ $logoLight }}" alt="{{ $blogName }}" class="h-8 w-auto dark:hidden">
                    <img src="{{ $logoDark }}" alt="{{ $blogName }}" class="h-8 w-auto hidden dark:block">
                @else
                    {{ $blogName }}
                @endif
            </a>

            {{-- Desktop nav --}}
            <div class="hidden sm:flex items-center gap-6 ml-auto">
                @foreach ($navPages as $navPage)
                    <a href="{{ $navUrl($navPage) }}" class="{{ $isNavActive($navPage) ? 'text-neutral-900 dark:text-neutral-100 font-medium' : 'text-neutral-600 dark:text-neutral-400' }} hover:text-accent dark:hover:text-accent transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ $navPage->title }}</a>
                @endforeach

                {{-- Divider --}}
                <span class="h-5 w-px bg-neutral-300 dark:bg-neutral-700" aria-hidden="true"></span>

                {{-- Utility group: Dark mode + Language --}}
                <div class="flex items-center gap-1 -ml-2">
                    {{-- Dark mode toggle --}}
                    <button
                        x-data="{ dark: document.documentElement.classList.contains('dark'), spinning: false }"
                        x-on:click="
                            spinning = true;
                            dark = !dark;
                            document.documentElement.classList.toggle('dark', dark);
                            localStorage.theme = dark ? 'dark' : 'light';
                            setTimeout(() => spinning = false, 400);
                        "
                        class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                        aria-label="{{ __('Toggle dark mode') }}"
                    >
                        <svg x-show="dark" class="w-5 h-5 dark-toggle-icon" :class="spinning && 'rotating'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!dark" class="w-5 h-5 dark-toggle-icon" :class="spinning && 'rotating'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    {{-- Language switcher --}}
                    <a
                        href="{{ route('locale.switch', $currentLocale === 'de' ? 'en' : 'de') }}"
                        class="flex items-center gap-1 p-2 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                        title="{{ __('Language') }}"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a15.3 15.3 0 014 9 15.3 15.3 0 01-4 9 15.3 15.3 0 01-4-9 15.3 15.3 0 014-9z" />
                        </svg>
                        <span class="text-xs font-medium uppercase tracking-wide">{{ $currentLocale === 'de' ? 'EN' : 'DE' }}</span>
                    </a>
                </div>
            </div>

            {{-- Mobile controls --}}
            <div class="flex items-center sm:hidden gap-1 ml-auto">
                {{-- Mobile dark mode toggle --}}
                <button
                    x-data="{ dark: document.documentElement.classList.contains('dark'), spinning: false }"
                    x-on:click="
                        spinning = true;
                        dark = !dark;
                        document.documentElement.classList.toggle('dark', dark);
                        localStorage.theme = dark ? 'dark' : 'light';
                        setTimeout(() => spinning = false, 400);
                    "
                    class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                    aria-label="{{ __('Toggle dark mode') }}"
                >
                    <svg x-show="dark" class="w-5 h-5 dark-toggle-icon" :class="spinning && 'rotating'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="!dark" class="w-5 h-5 dark-toggle-icon" :class="spinning && 'rotating'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                {{-- Hamburger button --}}
                <button
                    x-on:click="open = !open"
                    :aria-expanded="open.toString()"
                    aria-controls="mobile-menu"
                    class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                    aria-label="{{ __('Menu') }}"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-transition id="mobile-menu" class="sm:hidden pb-4">
            @foreach ($navPages as $navPage)
                <a href="{{ $navUrl($navPage) }}" class="block py-2 {{ $isNavActive($navPage) ? 'text-neutral-900 dark:text-neutral-100 font-medium' : 'text-neutral-600 dark:text-neutral-400' }} hover:text-accent dark:hover:text-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ $navPage->title }}</a>
            @endforeach

            <div class="mt-2 pt-2 border-t border-neutral-200 dark:border-neutral-800">
                <a
                    href="{{ route('locale.switch', $currentLocale === 'de' ? 'en' : 'de') }}"
                    class="flex items-center gap-2 py-2 text-neutral-600 dark:text-neutral-400 hover:text-accent dark:hover:text-accent transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm"
                    title="{{ __('Language') }}"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a15.3 15.3 0 014 9 15.3 15.3 0 01-4 9 15.3 15.3 0 01-4-9 15.3 15.3 0 014-9z" />
                    </svg>
                    {{ $currentLocale === 'de' ? 'English' : 'Deutsch' }}
                </a>
            </div>
        </div>
    </nav>
</header>
