<x-layout
    :title="$post->title . ' - ' . config('app.name')"
    :description="$post->excerpt"
    :og-title="$post->title"
    :og-description="$post->excerpt"
    :og-image="$post->getFirstMediaUrl('featured-image') ?: null"
    og-type="article"
    :edit-url="url('/admin/posts/' . $post->slug . '/edit')"
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

    {{-- Reading progress bar --}}
    <div
        x-data="readingProgress"
        x-on:scroll.window.throttle.50ms="update"
        x-show="progress > 0 && progress < 100"
        x-transition.opacity
        class="reading-progress"
        :style="'width: ' + progress + '%'"
    ></div>

    <article>
        <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-100">
            {{ $post->title }}
        </h1>

        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-muted dark:text-muted-dark">
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
            <span>{{ __(':count min read', ['count' => $post->reading_time]) }}</span>

            @if (\App\Models\Setting::get('comments_enabled', '1') === '1' && $post->comments_enabled)
                @php $commentCount = $post->approved_comments_count ?? 0; @endphp
                <span>&middot;</span>
                <a href="#comments" class="hover:text-accent transition-colors">
                    {{ $commentCount > 0 ? trans_choice('{1} 1 comment|[2,*] :count comments', $commentCount, ['count' => $commentCount]) : __(':count comments', ['count' => 0]) }}
                </a>
            @endif
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

        <div x-data="tableOfContents" data-toc-enabled="{{ $post->toc_enabled ? 'true' : 'false' }}">
            <template x-if="visible">
                <nav class="toc mt-8" :aria-label="'{{ __('Table of contents') }}'">
                    <div class="toc-header" @click="toggle()">
                        <p class="toc-title">{{ __('Table of contents') }}</p>
                        <svg class="toc-chevron" :class="open ? 'open' : ''" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <ol :class="open ? '' : 'collapsed'" :style="open ? 'max-height: 200rem' : ''">
                        <template x-for="(item, index) in items" :key="item.id">
                            <li :class="item.level === 3 ? 'toc-item toc-h3' : 'toc-item'">
                                <a
                                    :href="'#' + item.id"
                                    :class="activeId === item.id ? 'toc-link toc-link-active' : 'toc-link'"
                                    x-text="(index + 1) + '. ' + item.text"
                                ></a>
                            </li>
                        </template>
                    </ol>
                </nav>
            </template>

            <div x-data="codeBlocks" data-copy-label="{{ __('Copy code') }}" data-copied-label="{{ __('Copied!') }}">
                <x-prose class="mt-8">
                    {!! str($post->body)->sanitizeHtml() !!}
                </x-prose>
            </div>
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

    @php $newsletterEnabled = \App\Models\Setting::get('newsletter_enabled', '0') === '1'; @endphp

    @if ($post->author)
        <div class="mt-14 rounded-xl p-6" style="background-color: var(--color-card)">
            <div class="flex flex-row items-start gap-4">
                @if ($post->author->getFirstMediaUrl('avatar'))
                    <img
                        src="{{ $post->author->getFirstMediaUrl('avatar') }}"
                        alt="{{ $post->author->name }}"
                        class="h-10 w-10 sm:h-14 sm:w-14 shrink-0 rounded-full object-cover"
                        loading="lazy"
                    >
                @else
                    <div class="flex h-10 w-10 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-lg font-bold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
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

    @if ($newsletterEnabled)
        <div class="mt-6 rounded-xl p-6" style="background-color: var(--color-card)">
            <livewire:newsletter-subscribe variant="card" />
        </div>
    @endif

    {{-- Comments --}}
    @if (\App\Models\Setting::get('comments_enabled', '1') === '1' && $post->comments_enabled)
        <livewire:comments :post="$post" />
    @endif

    @if ($previousPost || $nextPost)
        <nav class="mt-14 pt-8 border-t border-neutral-200 dark:border-neutral-800 grid grid-cols-2 gap-4" aria-label="{{ __('Post navigation') }}">
            <div>
                @if ($previousPost)
                    <a href="{{ route('blog.show', $previousPost->slug) }}" class="group focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                        <span class="text-xs uppercase tracking-wide text-muted dark:text-muted-dark">{{ __('Previous post') }}</span>
                        <p class="text-accent group-hover:underline underline-offset-2 decoration-1 line-clamp-2">&larr; {{ $previousPost->title }}</p>
                    </a>
                @endif
            </div>

            <div class="text-right">
                @if ($nextPost)
                    <a href="{{ route('blog.show', $nextPost->slug) }}" class="group focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent rounded-sm">
                        <span class="text-xs uppercase tracking-wide text-muted dark:text-muted-dark">{{ __('Next post') }}</span>
                        <p class="text-accent group-hover:underline underline-offset-2 decoration-1 line-clamp-2">{{ $nextPost->title }} &rarr;</p>
                    </a>
                @endif
            </div>
        </nav>
    @endif
</x-layout>
