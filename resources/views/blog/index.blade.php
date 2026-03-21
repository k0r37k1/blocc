<x-layout :title="config('app.name')" :description="config('app.description')">
    <div class="mb-10">
        <h1 class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ \App\Models\Setting::get('blog_name', config('app.name')) }}
        </h1>
        @if ($description = \App\Models\Setting::get('blog_description'))
            <p class="mt-1 text-muted dark:text-muted-dark">{{ $description }}</p>
        @endif
    </div>

    <div class="space-y-2">
        @forelse ($posts as $post)
            <x-post-card :post="$post" :index="$loop->index" />
        @empty
            <p class="py-12 text-center text-neutral-500 dark:text-neutral-400">
                {{ __('No posts yet.') }}
            </p>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}

    @if (\App\Models\Setting::get('newsletter_enabled', '0') === '1')
        <div class="mt-10 -mx-4 rounded-lg px-4 py-6" style="background-color: var(--color-card)">
            <livewire:newsletter-subscribe variant="card" />
        </div>
    @endif
</x-layout>
