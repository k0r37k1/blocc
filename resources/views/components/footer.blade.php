@php
    $blogName = \App\Models\Setting::get('blog_name', config('app.name'));
    $footerText = \App\Models\Setting::get('footer_text');

    $socials = collect([
        'website' => \App\Models\Setting::get('social_website'),
        'github' => \App\Models\Setting::get('social_github'),
        'twitter' => \App\Models\Setting::get('social_twitter'),
        'linkedin' => \App\Models\Setting::get('social_linkedin'),
        'instagram' => \App\Models\Setting::get('social_instagram'),
        'bluesky' => \App\Models\Setting::get('social_bluesky'),
    ])->filter();
@endphp

<footer class="border-t border-neutral-200 dark:border-neutral-800">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 text-sm text-neutral-500 dark:text-neutral-400">
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
                <a href="{{ route('page.show', 'impressum') }}" class="hover:text-neutral-900 dark:hover:text-neutral-100">Impressum</a>
                <a href="{{ route('page.show', 'datenschutz') }}" class="hover:text-neutral-900 dark:hover:text-neutral-100">Datenschutz</a>
                @guest
                    <a href="{{ url('/admin/login') }}" class="hover:text-neutral-900 dark:hover:text-neutral-100">Anmelden</a>
                @endguest
            </div>
        </div>
    </div>
</footer>
