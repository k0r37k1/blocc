@php
    use Filament\Support\Icons\Heroicon;
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        @if ($this->getDraftCount() > 0)
            <div class="space-y-3">
                <div class="flex items-center gap-x-2">
                    <x-filament::icon
                        :icon="Heroicon::PencilSquare"
                        class="h-5 w-5 text-warning-500"
                    />
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                        {{ trans_choice('You have :count draft|You have :count drafts', $this->getDraftCount()) }}
                    </h3>
                </div>

                <ul class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($this->getDrafts() as $draft)
                        <li class="py-2">
                            <a
                                href="{{ $this->getEditUrl($draft) }}"
                                class="group flex items-center justify-between"
                            >
                                <span class="text-sm font-medium text-gray-700 group-hover:text-primary-600 dark:text-gray-300 dark:group-hover:text-primary-400">
                                    {{ $draft->title }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $draft->updated_at->diffForHumans() }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="flex items-center gap-x-2">
                <x-filament::icon
                    :icon="Heroicon::CheckCircle"
                    class="h-5 w-5 text-success-500"
                />
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('No drafts. All caught up!') }}
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
