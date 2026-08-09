<?php

namespace App\Observers;

use App\Models\Comment;
use App\Services\PostCache;
use Illuminate\Support\Facades\Cache;

class CommentObserver
{
    public function saved(Comment $comment): void
    {
        $this->forgetPostCache($comment);
    }

    public function deleted(Comment $comment): void
    {
        $this->forgetPostCache($comment);
    }

    private function forgetPostCache(Comment $comment): void
    {
        $post = $comment->post;

        if ($post === null) {
            return;
        }

        Cache::forget(PostCache::showKey($post->slug));
    }
}
