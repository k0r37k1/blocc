<div>
    @if ($successMessage)
        <p class="text-sm text-accent dark:text-accent" role="status">{{ $successMessage }}</p>
    @elseif ($errorMessage)
        <p class="text-sm text-red-600 dark:text-red-400" role="alert">{{ $errorMessage }}</p>
    @else
        <form wire:submit="subscribe" novalidate>
            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1">
                    <label for="newsletter-email" class="sr-only">{{ __('Email address') }}</label>
                    <input
                        id="newsletter-email"
                        type="email"
                        wire:model="email"
                        placeholder="{{ __('your@email.com') }}"
                        autocomplete="email"
                        class="w-full rounded-md border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-muted dark:placeholder-muted-dark focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-colors"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="shrink-0 rounded-md bg-accent-bg px-4 py-2 text-sm font-medium text-white hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-bg disabled:opacity-60 transition-opacity"
                >
                    <span wire:loading.remove>{{ __('Subscribe') }}</span>
                    <span wire:loading>{{ __('Sending…') }}</span>
                </button>
            </div>

            <div class="mt-2">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model="consent"
                        class="mt-0.5 h-3.5 w-3.5 shrink-0 rounded border-neutral-300 dark:border-neutral-600 text-accent-bg focus:ring-accent focus:ring-offset-0"
                    >
                    <span class="text-xs text-muted dark:text-muted-dark">
                        {!! __('I agree to receive the newsletter. I can unsubscribe at any time. See our <a href=":url" class="underline underline-offset-2 hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors">Privacy Policy</a>.', ['url' => route('page.show', 'datenschutz')]) !!}
                    </span>
                </label>
                @error('consent')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </form>
    @endif
</div>
