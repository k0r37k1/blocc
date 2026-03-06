<x-layout
    :title="$post->title . ' - Kopfsalat'"
    :description="$post->excerpt"
    :og-title="$post->title"
    :og-description="$post->excerpt"
    :og-image="$post->getFirstMediaUrl('featured-image') ?: null"
    og-type="article"
    :edit-url="url('/admin/posts/' . $post->id . '/edit')"
>
    <x-slot:meta>
        <meta property="article:published_time" content="{{ $post->published_at->toW3cString() }}">
        <meta property="article:modified_time" content="{{ $post->updated_at->toW3cString() }}">
        @foreach ($post->tags as $tag)
            <meta property="article:tag" content="{{ $tag->name }}">
        @endforeach
        @php
            $jsonLd = [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'description' => $post->excerpt,
                'datePublished' => $post->published_at->toW3cString(),
                'dateModified' => $post->updated_at->toW3cString(),
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => url()->current(),
                ],
            ];
            if ($post->author) {
                $jsonLd['author'] = ['@type' => 'Person', 'name' => $post->author->name];
            }
            if ($post->getFirstMediaUrl('featured-image')) {
                $jsonLd['image'] = $post->getFirstMediaUrl('featured-image');
            }
        @endphp
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-slot:meta>

    <article>
        <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ $post->title }}
        </h1>

        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
            @if ($post->author)
                <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $post->author->name }}</span>
                <span>&middot;</span>
            @endif

            <time datetime="{{ $post->published_at->toDateString() }}">
                {{ $post->published_at->translatedFormat('j. F Y') }}
            </time>

            @if ($post->category)
                <span>&middot;</span>
                <a href="{{ route('category.show', $post->category) }}" style="color: {{ $post->category->color }}">
                    {{ $post->category->name }}
                </a>
            @endif

            <span>&middot;</span>
            <span>{{ $post->reading_time }} Min. Lesezeit</span>
        </div>

        @if ($post->getFirstMediaUrl('featured-image'))
            <figure class="mt-6">
                <img
                    src="{{ $post->getFirstMediaUrl('featured-image', 'medium') }}"
                    alt="{{ $post->featured_image_alt ?? $post->title }}"
                    class="w-full aspect-video object-cover rounded-lg bg-neutral-100 dark:bg-neutral-800"
                    loading="lazy"
                    width="800"
                    height="450"
                >
            </figure>
        @endif

        <div x-data="codeBlocks">
            <x-prose class="mt-8">
                {!! str($post->body)->sanitizeHtml() !!}
            </x-prose>
        </div>

        @if ($post->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <a
                        href="{{ route('tag.show', $tag) }}"
                        class="inline-flex items-center rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600 hover:bg-accent/10 hover:text-accent dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-accent/10 dark:hover:text-accent"
                    >
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </article>

    @if ($post->author)
        <div class="mt-14 rounded-xl bg-neutral-100/60 p-6 dark:bg-neutral-900">
            <div class="flex flex-col sm:flex-row items-start gap-4">
                @if ($post->author->getFirstMediaUrl('avatar'))
                    <img
                        src="{{ $post->author->getFirstMediaUrl('avatar') }}"
                        alt="{{ $post->author->name }}"
                        class="h-14 w-14 shrink-0 rounded-full object-cover"
                        loading="lazy"
                    >
                @else
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-lg font-bold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                        {{ str($post->author->name)->substr(0, 1)->upper() }}
                    </div>
                @endif

                <div class="min-w-0">
                    <p class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $post->author->name }}</p>

                    @if ($post->author->bio)
                        <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">{{ $post->author->bio }}</p>
                    @endif

                    @php
                        $socials = collect([
                            'website' => $post->author->website,
                            'github' => $post->author->social_github,
                            'twitter' => $post->author->social_twitter,
                            'linkedin' => $post->author->social_linkedin,
                            'instagram' => $post->author->social_instagram,
                            'bluesky' => $post->author->social_bluesky,
                        ])->filter();
                    @endphp

                    @if ($socials->isNotEmpty())
                        <div class="mt-2 flex items-center gap-3">
                            @foreach ($socials as $platform => $url)
                                <x-social-icon :platform="$platform" :url="$url" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($previousPost || $nextPost)
        <nav class="mt-14 pt-8 border-t border-neutral-200 dark:border-neutral-800 grid grid-cols-2 gap-4">
            <div>
                @if ($previousPost)
                    <a href="{{ route('blog.show', $previousPost->slug) }}" class="group">
                        <span class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Vorheriger Beitrag</span>
                        <p class="text-accent group-hover:underline underline-offset-2 decoration-1 line-clamp-2">&larr; {{ $previousPost->title }}</p>
                    </a>
                @endif
            </div>

            <div class="text-right">
                @if ($nextPost)
                    <a href="{{ route('blog.show', $nextPost->slug) }}" class="group">
                        <span class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400">N&auml;chster Beitrag</span>
                        <p class="text-accent group-hover:underline underline-offset-2 decoration-1 line-clamp-2">{{ $nextPost->title }} &rarr;</p>
                    </a>
                @endif
            </div>
        </nav>
    @endif
</x-layout>
