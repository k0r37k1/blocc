<div>
    @if ($successMessage)
        <p class="text-xs text-accent dark:text-accent" role="status">{{ $successMessage }}</p>
    @elseif ($errorMessage)
        <p class="text-xs text-red-600 dark:text-red-400" role="alert">{{ $errorMessage }}</p>
    @else
        <form wire:submit="subscribe" novalidate>
            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="flex items-start gap-3 flex-wrap sm:flex-nowrap">
                <div class="flex-1 min-w-0">
                    <label for="newsletter-email" class="sr-only">{{ __('Email address') }}</label>
                    <input
                        id="newsletter-email"
                        type="email"
                        wire:model="email"
                        placeholder="{{ __('Newsletter — your@email.com') }}"
                        autocomplete="email"
                        class="w-full bg-transparent text-xs text-neutral-700 dark:text-neutral-300 placeholder-muted dark:placeholder-muted-dark focus:outline-none focus:placeholder-transparent border-b border-neutral-200 dark:border-neutral-700 focus:border-accent dark:focus:border-accent pb-0.5 transition-colors"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="shrink-0 text-xs font-medium text-accent hover:underline underline-offset-2 disabled:opacity-50 transition-opacity focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm"
                >
                    <span wire:loading.remove>{{ __('Subscribe') }}</span>
                    <span wire:loading class="text-muted dark:text-muted-dark">{{ __('Sending…') }}</span>
                </button>
            </div>

            <div class="mt-1.5">
                <label class="flex items-start gap-1.5 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model="consent"
                        class="mt-px h-3 w-3 shrink-0 rounded-sm border-neutral-300 dark:border-neutral-600 text-accent-bg focus:ring-accent focus:ring-offset-0"
                    >
                    <span class="text-xs text-muted dark:text-muted-dark leading-snug">
                        {!! __('I agree. <a href=":url" class="underline underline-offset-2 hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors">Privacy Policy</a>.', ['url' => route('page.show', 'datenschutz')]) !!}
                    </span>
                </label>
                @error('consent')
                    <p class="mt-0.5 text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </form>
    @endif
</div>
