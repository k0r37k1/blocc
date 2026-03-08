@props(['post'])

@php
    $commentsEnabled = \App\Models\Setting::get('comments_enabled', '1') === '1';
    $appId = \App\Models\Setting::get('cusdis_app_id', '');
    $pageId = $post->slug;
    $pageUrl = url()->current();
    $pageTitle = $post->title;
@endphp

@if (! $commentsEnabled || blank($appId))
    @return
@endif

<div
    id="comments"
    x-data="comments('{{ $appId }}', '{{ $pageId }}', '{{ $pageUrl }}', '{{ $pageTitle }}', { commentSent: '{{ __('Comment sent! It will appear after approval.') }}', replySent: '{{ __('Reply sent! It will appear after approval.') }}' })"
    x-init="fetchComments()"
    class="mt-14 pt-8 border-t border-neutral-200 dark:border-neutral-900"
>
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-6">
        {{ __('Comments') }}
        <span x-show="commentCount > 0" x-text="'(' + commentCount + ')'" class="text-sm font-normal text-muted dark:text-muted-dark"></span>
    </h2>

    {{-- Comment list --}}
    <div x-show="loading" class="text-sm text-muted dark:text-muted-dark">{{ __('Loading comments...') }}</div>

    <div x-show="!loading && items.length === 0" class="text-sm text-muted dark:text-muted-dark">
        {{ __('No comments yet. Be the first!') }}
    </div>

    <div x-show="!loading" class="space-y-6">
        <template x-for="comment in items" :key="comment.id">
            <div class="group">
                <div class="flex items-start gap-3">
                    {{-- Avatar --}}
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800 text-sm font-semibold text-neutral-500 dark:text-neutral-400" x-text="comment.by_nickname.charAt(0).toUpperCase()"></div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100" x-text="comment.by_nickname"></span>
                            <span class="text-xs text-muted dark:text-muted-dark" x-text="formatDate(comment.createdAt)"></span>
                        </div>
                        <div class="mt-1 text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed whitespace-pre-line" x-text="comment.content"></div>

                        <button
                            x-show="replyingTo !== comment.id"
                            @click="replyingTo = comment.id"
                            class="mt-2 text-xs text-muted dark:text-muted-dark hover:text-accent transition-colors"
                        >
                            {{ __('Reply') }}
                        </button>

                        {{-- Reply form --}}
                        <div x-show="replyingTo === comment.id" x-transition class="mt-3">
                            <form @submit.prevent="submitReply(comment.id)" class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <input
                                        type="text"
                                        x-model="replyForm.nickname"
                                        placeholder="{{ __('Nickname') }}"
                                        required
                                        class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                                    >
                                    <input
                                        type="email"
                                        x-model="replyForm.email"
                                        placeholder="{{ __('Email (optional)') }}"
                                        class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                                    >
                                </div>
                                <textarea
                                    x-model="replyForm.content"
                                    placeholder="{{ __('Your reply...') }}"
                                    required
                                    rows="2"
                                    class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
                                ></textarea>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="submit"
                                        :disabled="submitting"
                                        class="rounded-lg bg-accent px-4 py-1.5 text-sm font-medium text-white hover:opacity-90 transition-opacity disabled:opacity-50"
                                    >
                                        <span x-show="!submitting">{{ __('Reply') }}</span>
                                        <span x-show="submitting">{{ __('Sending...') }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="replyingTo = null"
                                        class="text-xs text-muted dark:text-muted-dark hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors"
                                    >
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Nested replies --}}
                        <template x-if="comment.replies && comment.replies.data && comment.replies.data.length > 0">
                            <div class="mt-4 space-y-4 pl-4 border-l-2 border-neutral-200 dark:border-neutral-900">
                                <template x-for="reply in comment.replies.data" :key="reply.id">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800 text-xs font-semibold text-neutral-500 dark:text-neutral-400" x-text="reply.by_nickname.charAt(0).toUpperCase()"></div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100" x-text="reply.by_nickname"></span>
                                                <span class="text-xs text-muted dark:text-muted-dark" x-text="formatDate(reply.createdAt)"></span>
                                            </div>
                                            <div class="mt-1 text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed whitespace-pre-line" x-text="reply.content"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Pagination --}}
    <div x-show="pageCount > 1" class="mt-6 flex items-center gap-2">
        <button
            @click="page > 1 && (page--, fetchComments())"
            :disabled="page <= 1"
            class="text-sm text-muted dark:text-muted-dark hover:text-neutral-900 dark:hover:text-neutral-100 disabled:opacity-30 transition-colors"
        >
            &larr;
        </button>
        <span class="text-xs text-muted dark:text-muted-dark" x-text="page + ' / ' + pageCount"></span>
        <button
            @click="page < pageCount && (page++, fetchComments())"
            :disabled="page >= pageCount"
            class="text-sm text-muted dark:text-muted-dark hover:text-neutral-900 dark:hover:text-neutral-100 disabled:opacity-30 transition-colors"
        >
            &rarr;
        </button>
    </div>

    {{-- New comment form --}}
    <form @submit.prevent="submitComment()" class="mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-900 space-y-3">
        <div class="grid grid-cols-2 gap-3">
            <input
                type="text"
                x-model="form.nickname"
                placeholder="{{ __('Nickname') }}"
                required
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
            >
            <input
                type="email"
                x-model="form.email"
                placeholder="{{ __('Email (optional)') }}"
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
            >
        </div>
        <textarea
            x-model="form.content"
            placeholder="{{ __('Write a comment...') }}"
            required
            rows="3"
            class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
        ></textarea>
        <div class="flex items-center justify-between">
            <p class="text-xs text-muted dark:text-muted-dark">{{ __('Comments are moderated before appearing.') }}</p>
            <button
                type="submit"
                :disabled="submitting"
                class="rounded-lg bg-accent px-5 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity disabled:opacity-50"
            >
                <span x-show="!submitting">{{ __('Send') }}</span>
                <span x-show="submitting">{{ __('Sending...') }}</span>
            </button>
        </div>
    </form>

    {{-- Success message --}}
    <div
        x-show="successMessage"
        x-transition
        class="mt-4 rounded-lg bg-accent/10 px-4 py-3 text-sm text-accent"
        x-text="successMessage"
    ></div>
</div>
