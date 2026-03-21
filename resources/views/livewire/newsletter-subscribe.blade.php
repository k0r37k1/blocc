<div x-data x-on:pageshow.window="if ($event.persisted) { $wire.successMessage = ''; $wire.errorMessage = ''; }">
    @if ($successMessage)
        <p class="text-sm text-accent" role="status">{{ $successMessage }}</p>
    @elseif ($errorMessage)
        <p class="text-sm text-red-600 dark:text-red-400" role="alert">{{ $errorMessage }}</p>
    @else
        <form wire:submit="subscribe" novalidate>
            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($variant === 'card')
                {{-- Card variant: heading left + input with arrow button --}}
                <div class="flex items-center gap-4">
                    <p id="newsletter-label" class="shrink-0 text-xl font-bold text-neutral-900 dark:text-neutral-100">{{ __('Newsletter') }}</p>

                    <div class="relative flex-1">
                        <input
                            id="newsletter-email"
                            type="email"
                            wire:model="email"
                            placeholder="{{ __('your@email.com') }}"
                            autocomplete="email"
                            aria-labelledby="newsletter-label"
                            class="w-full py-2 text-sm border-b border-neutral-200 dark:border-neutral-700 bg-transparent text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:outline-none focus:border-accent dark:focus:border-accent transition-colors"
                        >
                        @error('email')
                            <p class="absolute right-0 top-full mt-0.5 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:loading.attr="aria-busy"
                        class="shrink-0 p-2.5 rounded-md text-white disabled:opacity-50 transition-opacity focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                        style="background-color: var(--color-accent-bg)"
                        aria-label="{{ __('Subscribe') }}"
                    >
                        <span wire:loading.remove aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                        <span wire:loading aria-hidden="true">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            @else
                {{-- Footer variant: underline input + text button --}}
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mb-2">{{ __('Newsletter') }}</p>

                <div class="flex items-center gap-3">
                    <label for="newsletter-email" class="sr-only">{{ __('Email address') }}</label>
                    <input
                        id="newsletter-email"
                        type="email"
                        wire:model="email"
                        placeholder="{{ __('your@email.com') }}"
                        autocomplete="email"
                        class="min-w-0 flex-1 text-sm bg-transparent text-neutral-700 dark:text-neutral-300 placeholder-neutral-400 dark:placeholder-neutral-600 border-b border-neutral-200 dark:border-neutral-700 focus:border-accent dark:focus:border-accent focus:outline-none focus:placeholder-transparent pb-1 transition-colors"
                    >
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="shrink-0 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 disabled:opacity-50 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm"
                    >
                        <span wire:loading.remove>{{ __('Subscribe') }}</span>
                        <span wire:loading>{{ __('Sending…') }}</span>
                    </button>
                </div>

                @error('email')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            @endif
        </form>
    @endif
</div>
