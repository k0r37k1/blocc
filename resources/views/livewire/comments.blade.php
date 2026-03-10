<div
    id="comments"
    x-data="{
        tokens: JSON.parse(localStorage.getItem('comment_tokens') || '{}'),
        init() {
            Livewire.on('comment-token-stored', ({ commentId, token }) => {
                this.tokens[commentId] = token;
                localStorage.setItem('comment_tokens', JSON.stringify(this.tokens));
            });
        },
        canEdit(commentId, createdTimestamp) {
            if (!this.tokens[commentId]) return false;
            const now = Math.floor(Date.now() / 1000);
            return (now - createdTimestamp) < 3600;
        },
        startEditing(commentId) {
            const token = this.tokens[commentId];
            if (token) $wire.startEditing(commentId, token);
        },
        saveEdit(commentId) {
            const token = this.tokens[commentId];
            if (token) $wire.saveEdit(token);
        },
        deleteComment(commentId) {
            if (!confirm('{{ __('Are you sure you want to delete this comment?') }}')) return;
            const token = this.tokens[commentId];
            if (token) {
                $wire.deleteComment(commentId, token);
                delete this.tokens[commentId];
                localStorage.setItem('comment_tokens', JSON.stringify(this.tokens));
            }
        }
    }"
    class="mt-14 pt-8 border-t border-neutral-200 dark:border-neutral-900"
>
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-6">
        {{ __('Comments') }}
        @if ($comments->total() > 0)
            <span class="text-sm font-normal text-muted dark:text-muted-dark">({{ $comments->total() }})</span>
        @endif
    </h2>

    {{-- Comment list --}}
    @forelse ($comments as $comment)
        <div class="group mb-6" wire:key="comment-{{ $comment->id }}">
            <div class="flex items-start gap-3">
                {{-- Avatar --}}
                <img
                    src="{{ $comment->gravatar_url }}"
                    alt="{{ $comment->nickname }}"
                    loading="lazy"
                    class="h-9 w-9 shrink-0 rounded-full {{ $comment->is_author ? 'ring-2 ring-accent' : '' }}"
                >

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $comment->nickname }}
                        </span>
                        @if ($comment->is_author)
                            <span class="inline-flex items-center rounded-full bg-accent/10 px-2 py-0.5 text-xs font-medium text-accent">
                                {{ __('Author') }}
                            </span>
                        @endif
                        <span class="text-xs text-muted dark:text-muted-dark">
                            {{ $comment->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Content or Edit Form --}}
                    @if ($editingCommentId === $comment->id)
                        <div class="mt-2">
                            <textarea
                                wire:model="editContent"
                                rows="3"
                                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
                            ></textarea>
                            @error('editContent') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            <div class="mt-2 flex items-center gap-2">
                                <button
                                    x-on:click="saveEdit({{ $comment->id }})"
                                    class="rounded-lg bg-accent-bg px-4 py-1.5 text-sm font-medium text-white hover:opacity-90 transition-opacity"
                                >
                                    {{ __('Save') }}
                                </button>
                                <button
                                    wire:click="cancelEdit"
                                    type="button"
                                    class="text-xs text-muted dark:text-muted-dark hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors"
                                >
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mt-1 text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed whitespace-pre-line break-words">{{ $comment->content }}</div>

                        <div class="mt-2 flex items-center gap-3">
                            <button
                                wire:click="startReply({{ $comment->id }})"
                                x-on:click="$nextTick(() => { $refs.commentForm.scrollIntoView({ behavior: 'smooth', block: 'center' }); $refs.content.focus(); })"
                                class="text-xs text-muted dark:text-muted-dark hover:text-accent transition-colors"
                            >
                                {{ __('Reply') }}
                            </button>

                            {{-- Edit/Delete buttons (client-side token check) --}}
                            <template x-if="canEdit({{ $comment->id }}, {{ $comment->created_at->timestamp }})">
                                <div class="flex items-center gap-3">
                                    <button
                                        x-on:click="startEditing({{ $comment->id }})"
                                        class="text-xs text-muted dark:text-muted-dark hover:text-accent transition-colors"
                                    >
                                        {{ __('Edit') }}
                                    </button>
                                    <button
                                        x-on:click="deleteComment({{ $comment->id }})"
                                        class="text-xs text-muted dark:text-muted-dark hover:text-red-500 transition-colors"
                                    >
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            </template>
                        </div>
                    @endif

                    {{-- Nested replies --}}
                    @if ($comment->replies->isNotEmpty())
                        <div class="mt-4 space-y-4 pl-4 border-l-2 border-neutral-200 dark:border-neutral-900">
                            @foreach ($comment->replies as $reply)
                                <div class="flex items-start gap-3" wire:key="reply-{{ $reply->id }}">
                                    <img
                                        src="{{ $reply->gravatar_url }}"
                                        alt="{{ $reply->nickname }}"
                                        loading="lazy"
                                        class="h-7 w-7 shrink-0 rounded-full {{ $reply->is_author ? 'ring-2 ring-accent' : '' }}"
                                    >
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $reply->nickname }}</span>
                                            @if ($reply->is_author)
                                                <span class="inline-flex items-center rounded-full bg-accent/10 px-2 py-0.5 text-xs font-medium text-accent">
                                                    {{ __('Author') }}
                                                </span>
                                            @endif
                                            <span class="text-xs text-muted dark:text-muted-dark">{{ $reply->created_at->diffForHumans() }}</span>
                                        </div>

                                        @if ($editingCommentId === $reply->id)
                                            <div class="mt-2">
                                                <textarea
                                                    wire:model="editContent"
                                                    rows="2"
                                                    class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
                                                ></textarea>
                                                @error('editContent') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                                <div class="mt-2 flex items-center gap-2">
                                                    <button
                                                        x-on:click="saveEdit({{ $reply->id }})"
                                                        class="rounded-lg bg-accent-bg px-4 py-1.5 text-sm font-medium text-white hover:opacity-90 transition-opacity"
                                                    >
                                                        {{ __('Save') }}
                                                    </button>
                                                    <button
                                                        wire:click="cancelEdit"
                                                        type="button"
                                                        class="text-xs text-muted dark:text-muted-dark hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors"
                                                    >
                                                        {{ __('Cancel') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-1 text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed whitespace-pre-line">{{ $reply->content }}</div>

                                            {{-- Edit/Delete for replies --}}
                                            <template x-if="canEdit({{ $reply->id }}, {{ $reply->created_at->timestamp }})">
                                                <div class="mt-2 flex items-center gap-3">
                                                    <button
                                                        x-on:click="startEditing({{ $reply->id }})"
                                                        class="text-xs text-muted dark:text-muted-dark hover:text-accent transition-colors"
                                                    >
                                                        {{ __('Edit') }}
                                                    </button>
                                                    <button
                                                        x-on:click="deleteComment({{ $reply->id }})"
                                                        class="text-xs text-muted dark:text-muted-dark hover:text-red-500 transition-colors"
                                                    >
                                                        {{ __('Delete') }}
                                                    </button>
                                                </div>
                                            </template>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-muted dark:text-muted-dark">
            {{ __('No comments yet. Be the first!') }}
        </p>
    @endforelse

    {{-- Pagination --}}
    @if ($comments->hasPages())
        <div class="mt-6">
            {{ $comments->links() }}
        </div>
    @endif

    {{-- Comment form (also used for replies) --}}
    <form wire:submit="submitComment" x-ref="commentForm" class="mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-900 space-y-3">
        @if ($replyingTo)
            <div class="flex items-center gap-2 text-sm text-accent">
                <span>{{ __('Replying to :name', ['name' => $replyingToNickname]) }}</span>
                <button
                    type="button"
                    wire:click="cancelReply"
                    class="text-xs text-muted dark:text-muted-dark hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors"
                >
                    {{ __('Cancel') }}
                </button>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-3">
            <div>
                <input
                    type="text"
                    wire:model="nickname"
                    placeholder="{{ __('Nickname') }}"
                    @auth disabled @endauth
                    class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed"
                >
                @error('nickname') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <input
                    type="email"
                    wire:model="email"
                    placeholder="{{ __('Email (optional)') }}"
                    @auth disabled @endauth
                    class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed"
                >
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>
        <div
            x-data="{
                showEmojis: false,
                isDark: document.documentElement.classList.contains('dark'),
                _observer: null,
                init() {
                    this._observer = new MutationObserver(() => {
                        this.isDark = document.documentElement.classList.contains('dark');
                    });
                    this._observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                },
                destroy() { this._observer?.disconnect() },
                get pickerBg() { return this.isDark ? '#27272a' : '#fff' },
                get pickerBorder() { return this.isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)' },
                get hoverBg() { return this.isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.06)' },
                insertEmoji(emoji) {
                    const ta = $refs.content;
                    const start = ta.selectionStart;
                    const end = ta.selectionEnd;
                    ta.value = ta.value.substring(0, start) + emoji + ta.value.substring(end);
                    ta.selectionStart = ta.selectionEnd = start + emoji.length;
                    ta.focus();
                    ta.dispatchEvent(new Event('input'));
                    this.showEmojis = false;
                }
            }"
            x-on:keydown.escape.window="showEmojis = false"
            style="position: relative;"
        >
            <textarea
                x-ref="content"
                wire:model="content"
                placeholder="{{ $replyingTo ? __('Your reply...') : __('Write a comment...') }}"
                rows="3"
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
                style="padding-right: 2.5rem;"
            ></textarea>
            {{-- Emoji toggle --}}
            <button
                type="button"
                x-on:click="showEmojis = !showEmojis"
                style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem; color: var(--color-muted); transition: color 0.15s;"
                x-on:mouseenter="$el.style.color='var(--color-accent)'"
                x-on:mouseleave="$el.style.color='var(--color-muted)'"
                title="{{ __('Emojis') }}"
                aria-label="{{ __('Emojis') }}"
                x-bind:aria-expanded="showEmojis.toString()"
            >
                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                </svg>
            </button>
            {{-- Emoji picker --}}
            <div
                x-show="showEmojis"
                x-on:click.outside="showEmojis = false"
                x-transition.opacity
                style="position: absolute; right: 0; bottom: 100%; margin-bottom: 0.25rem; z-index: 10;"
            >
                <div
                    x-bind:style="'display:grid;width:20rem;grid-template-columns:repeat(8,1fr);gap:0.125rem;padding:0.5rem;border-radius:0.5rem;border:1px solid '+pickerBorder+';background:'+pickerBg+';box-shadow:0 4px 12px rgba(0,0,0,0.15);'"
                    role="group"
                    aria-label="{{ __('Emoji picker') }}"
                >
                    @foreach (config('emojis.picker') as $emoji)
                        <button
                            type="button"
                            style="padding: 0.375rem; font-size: 1.25rem; line-height: 1; border-radius: 0.25rem; cursor: pointer; border: none; background: transparent; transition: background 0.1s;"
                            x-on:mouseenter="$el.style.background = hoverBg"
                            x-on:mouseleave="$el.style.background = 'transparent'"
                            x-on:click="insertEmoji('{{ $emoji }}')"
                            aria-label="{{ $emoji }}"
                        >{{ $emoji }}</button>
                    @endforeach
                </div>
            </div>
            @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        {{-- Honeypot --}}
        <div class="hidden" aria-hidden="true">
            <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
        </div>
        <div class="flex items-center justify-between">
            <p class="text-xs text-muted dark:text-muted-dark">{{ __('Comments are moderated before appearing.') }}</p>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="rounded-lg bg-accent-bg px-5 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="submitComment">{{ $replyingTo ? __('Reply') : __('Send') }}</span>
                <span wire:loading wire:target="submitComment">{{ __('Sending...') }}</span>
            </button>
        </div>
    </form>

    {{-- Success message --}}
    @if (filled($successMessage))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => { show = false; $wire.set('successMessage', '') }, 5000)"
            x-show="show"
            x-transition
            class="mt-4 rounded-lg bg-accent/10 px-4 py-3 text-sm text-accent"
        >
            {{ $successMessage }}
        </div>
    @endif
</div>
