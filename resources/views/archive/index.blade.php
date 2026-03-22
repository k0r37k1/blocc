<x-layout :title="__('Archive') . ' - ' . config('app.name')" :description="__('Archive')">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-8">{{ __('Archive') }}</h1>

    <livewire:archive-list />
</x-layout>
