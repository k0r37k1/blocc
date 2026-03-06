<x-layout :title="$page->title . ' - ' . config('app.name')" :description="\Illuminate\Support\Str::limit(strip_tags($page->body_raw ?? $page->body), 160)" :edit-url="url('/admin/pages/' . $page->slug . '/edit')">
    <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">{{ $page->title }}</h1>

    <div x-data="codeBlocks">
        <x-prose class="mt-6">
            {!! str($page->body)->sanitizeHtml() !!}
        </x-prose>
    </div>
</x-layout>
