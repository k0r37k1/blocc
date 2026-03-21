<x-layout :title="config('app.name')" :description="config('app.description')">
    <div class="mb-16">
        <h1 class="text-4xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ \App\Models\Setting::get('blog_name', config('app.name')) }}
        </h1>
        @if ($description = \App\Models\Setting::get('blog_description'))
            <p class="mt-1 text-muted dark:text-muted-dark">{{ $description }}</p>
        @endif
    </div>

    <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
        @forelse ($posts as $post)
            <div class="py-8 first:pt-0">
                <x-post-card :post="$post" :index="$loop->index" />
            </div>
        @empty
            <p class="py-12 text-center text-neutral-500 dark:text-neutral-400">
                {{ __('No posts yet.') }}
            </p>
        @endforelse
    </div>

    {{ $posts->links('components.pagination') }}

    @if (\App\Models\Setting::get('newsletter_enabled', '0') === '1')
        <div class="mt-16 rounded-lg px-4 py-5" style="background-color: var(--color-card)">
            <livewire:newsletter-subscribe variant="card" />
        </div>
    @endif
</x-layout>
