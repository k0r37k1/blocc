@php
    $blogName = \App\Models\Setting::get('blog_name', config('app.name'));
    $footerText = \App\Models\Setting::get('footer_text');
    $newsletterEnabled = \App\Models\Setting::get('newsletter_enabled', '0') === '1';

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
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 text-sm text-muted dark:text-muted-dark">
        @if ($newsletterEnabled)
            <div class="mb-6 pb-6 border-b border-neutral-100 dark:border-neutral-900">
                <livewire:newsletter-subscribe />
            </div>
        @endif
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} {{ $blogName }}</p>

            @if ($socials->isNotEmpty())
                <div class="flex items-center gap-4">
                    @foreach ($socials as $platform => $url)
                        <x-social-icon :platform="$platform" :url="$url" class="w-5 h-5" />
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            @if ($footerText)
                <p>{{ $footerText }}</p>
            @else
                <span></span>
            @endif
            <div class="flex gap-4">
                <a href="{{ route('page.show', 'impressum') }}" class="underline decoration-neutral-300 dark:decoration-neutral-600 underline-offset-2 hover:text-neutral-900 dark:hover:text-neutral-100 hover:decoration-current transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Imprint') }}</a>
                <a href="{{ route('page.show', 'datenschutz') }}" class="underline decoration-neutral-300 dark:decoration-neutral-600 underline-offset-2 hover:text-neutral-900 dark:hover:text-neutral-100 hover:decoration-current transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Privacy') }}</a>
                <a href="{{ route('page.show', 'barrierefreiheit') }}" class="underline decoration-neutral-300 dark:decoration-neutral-600 underline-offset-2 hover:text-neutral-900 dark:hover:text-neutral-100 hover:decoration-current transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Accessibility') }}</a>
                @guest
                    <a href="{{ url('/admin/login') }}" class="underline decoration-neutral-300 dark:decoration-neutral-600 underline-offset-2 hover:text-neutral-900 dark:hover:text-neutral-100 hover:decoration-current transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">{{ __('Login') }}</a>
                @endguest
            </div>
        </div>
    </div>
</footer>
