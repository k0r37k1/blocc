<x-filament-panels::page wire:poll.60s="autosave">
    @if ($label = $this->getLastAutosavedLabel())
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400" wire:ignore.self>
            {{ __('Last saved at :time', ['time' => $label]) }}
        </p>
    @endif

    {{ $this->content }}
</x-filament-panels::page>
