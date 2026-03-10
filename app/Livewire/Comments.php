<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Comments extends Component
{
    use WithPagination;

    #[Locked]
    public int $postId;

    public string $nickname = '';

    public string $email = '';

    public string $content = '';

    /** Honeypot field — must remain empty. */
    public string $website = '';

    /** Timestamp when form was rendered (spam time check). */
    #[Locked]
    public int $formLoadedAt = 0;

    /** Reply state */
    public ?int $replyingTo = null;

    public string $replyingToNickname = '';

    /** Edit state */
    public ?int $editingCommentId = null;

    public string $editContent = '';

    public string $successMessage = '';

    public function mount(Post $post): void
    {
        $this->postId = $post->id;
        $this->formLoadedAt = now()->timestamp;

        if ($user = Auth::user()) {
            $this->nickname = $user->name;
            $this->email = $user->email;
        }
    }

    public function render(): View
    {
        $comments = Comment::query()
            ->where('post_id', $this->postId)
            ->approved()
            ->topLevel()
            ->with(['replies' => fn ($query) => $query->approved()->oldest()])
            ->latest()
            ->paginate(10);

        return view('livewire.comments', [
            'comments' => $comments,
        ]);
    }

    public function submitComment(): void
    {
        $isAdmin = Auth::check();

        if (! $isAdmin && $this->isSpam()) {
            $this->successMessage = $this->replyingTo
                ? __('Reply sent! It will appear after approval.')
                : __('Comment sent! It will appear after approval.');
            $this->replyingTo = null;
            $this->replyingToNickname = '';

            return;
        }

        $this->validate([
            'nickname' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'content' => 'required|string|min:3|max:2000',
        ]);

        if (! $isAdmin && $this->hasExcessiveLinks($this->content)) {
            $this->addError('content', __('Too many links in your comment.'));

            return;
        }

        if (! $isAdmin) {
            $rateLimitKey = 'comment:'.request()->ip();

            if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
                $this->addError('content', __('Too many comments. Please try again later.'));

                return;
            }

            RateLimiter::hit($rateLimitKey, 600);
        }

        $parentId = null;
        if ($this->replyingTo) {
            $parent = Comment::where('id', $this->replyingTo)
                ->where('post_id', $this->postId)
                ->approved()
                ->topLevel()
                ->exists();

            $parentId = $parent ? $this->replyingTo : null;
        }

        $editToken = Str::random(64);

        $comment = Comment::create([
            'post_id' => $this->postId,
            'parent_id' => $parentId,
            'nickname' => $this->nickname,
            'email' => $this->email ?: null,
            'content' => $this->content,
            'ip_address' => request()->ip(),
            'edit_token' => $editToken,
            'is_approved' => $isAdmin,
            'is_author' => $isAdmin,
        ]);

        $this->dispatch('comment-token-stored', commentId: $comment->id, token: $editToken);

        $isReply = (bool) $parentId;
        $this->reset('content', 'replyingTo', 'replyingToNickname');

        if ($isAdmin) {
            $this->successMessage = $isReply ? __('Reply published.') : __('Comment published.');
        } else {
            $this->successMessage = $isReply
                ? __('Reply sent! It will appear after approval.')
                : __('Comment sent! It will appear after approval.');
            $this->notifyAdmin($comment->load('post'));
        }
    }

    public function startEditing(int $commentId, string $editToken): void
    {
        $comment = Comment::find($commentId);

        if (! $comment || ! hash_equals($comment->edit_token, $editToken) || ! $comment->isEditable()) {
            return;
        }

        $this->editingCommentId = $commentId;
        $this->editContent = $comment->content;
    }

    public function saveEdit(string $editToken): void
    {
        $this->validate(['editContent' => 'required|string|min:3|max:2000']);

        $comment = Comment::find($this->editingCommentId);

        if (! $comment || ! hash_equals($comment->edit_token, $editToken) || ! $comment->isEditable()) {
            $this->cancelEdit();

            return;
        }

        $comment->update(['content' => $this->editContent]);
        $this->cancelEdit();
    }

    public function deleteComment(int $commentId, string $editToken): void
    {
        $comment = Comment::find($commentId);

        if (! $comment || ! hash_equals($comment->edit_token, $editToken) || ! $comment->isEditable()) {
            return;
        }

        $comment->delete();
    }

    public function cancelEdit(): void
    {
        $this->editingCommentId = null;
        $this->editContent = '';
    }

    public function startReply(int $commentId): void
    {
        $comment = Comment::find($commentId);
        $this->replyingTo = $commentId;
        $this->replyingToNickname = $comment?->nickname ?? '';
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->replyingToNickname = '';
    }

    /**
     * Detect spam via honeypot and time check.
     */
    private function isSpam(): bool
    {
        // Honeypot filled = bot
        if (filled($this->website)) {
            return true;
        }

        // Form submitted too fast (< 3 seconds)
        if ($this->formLoadedAt > 0 && (now()->timestamp - $this->formLoadedAt) < 3) {
            return true;
        }

        return false;
    }

    /**
     * Check for excessive links (> 2).
     */
    private function hasExcessiveLinks(string $text): bool
    {
        $linkCount = preg_match_all('/https?:\/\//i', $text);

        return $linkCount > 2;
    }

    /**
     * Notify admin about new comment.
     */
    private function notifyAdmin(Comment $comment): void
    {
        $admin = User::first();

        if ($admin) {
            $admin->notify(new NewCommentNotification($comment));
        }
    }
}
