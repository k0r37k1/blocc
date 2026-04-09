@php
    $blogName = \App\Models\Setting::get('blog_name', config('app.name'));
    $footerText = \App\Models\Setting::get('footer_text');
    $newsletterEnabled = \App\Models\Setting::get('newsletter_enabled', '0') === '1';

    $footerPages = \App\Models\Page::query()->published()->where('show_in_footer', true)->orderBy('sort_order')->get();
    $owner = \App\Models\User::first();
    $socials = $owner ? collect([
        'website' => $owner->website,
        'github' => $owner->social_github,
        'twitter' => $owner->social_twitter,
        'linkedin' => $owner->social_linkedin,
        'instagram' => $owner->social_instagram,
        'bluesky' => $owner->social_bluesky,
    ])->filter() : collect();
@endphp

<footer class="border-t border-neutral-100 dark:border-neutral-900" role="contentinfo">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        <div class="py-6 text-sm text-muted dark:text-muted-dark">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; {{ date('Y') }} {{ $blogName }}</p>

                <div class="flex items-center gap-4">
                    @foreach ($socials as $platform => $url)
                        <x-social-icon :platform="$platform" :url="$url" class="w-5 h-5" />
                    @endforeach
                    <a href="{{ url('/feed') }}" class="text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm" aria-label="{{ __('RSS Feed') }}" title="{{ __('RSS Feed') }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19.01 7.38 20 6.18 20C4.98 20 4 19.01 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/></svg>
                    </a>
                    @guest
                        <a href="{{ route('filament.admin.auth.login') }}" class="text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm" aria-label="{{ __('Login') }}" title="{{ __('Login') }}">
                            <x-heroicon-o-arrow-left-end-on-rectangle class="w-5 h-5" aria-hidden="true" />
                        </a>
                    @endguest
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                @if ($footerText)
                    <p>{{ $footerText }}</p>
                @else
                    <span></span>
                @endif
                <div class="flex gap-4">
                    @foreach ($footerPages as $footerPage)
                        <a href="{{ route('page.show', $footerPage->slug) }}" class="underline decoration-neutral-300 dark:decoration-neutral-600 underline-offset-2 hover:text-neutral-900 dark:hover:text-neutral-100 hover:decoration-current transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ $footerPage->title }}</a>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</footer>
