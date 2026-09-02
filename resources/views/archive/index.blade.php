<x-layout :title="__('Archive') . ' - ' . config('app.name')" :description="__('Browse all posts by date.')">
    <header class="mb-10">
        <h1 class="text-2xl font-bold tracking-tight text-neutral-900 sm:text-3xl dark:text-neutral-100">
            {{ __('Archive') }}
        </h1>
        <p class="mt-3 text-sm text-muted dark:text-muted-dark">
            {{ trans_choice('{0} No posts|{1} :count post|[2,*] :count posts', $postCount, ['count' => $postCount]) }}
            · {{ __('Chronological list') }}
        </p>
    </header>

    <livewire:archive-list />
</x-layout>
