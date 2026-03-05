<x-layout :title="$page->title . ' - Kopfsalat'">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $page->title }}</h1>

    <div x-data="codeBlocks">
        <x-prose class="mt-6">
            {!! $page->body !!}
        </x-prose>
    </div>
</x-layout>
