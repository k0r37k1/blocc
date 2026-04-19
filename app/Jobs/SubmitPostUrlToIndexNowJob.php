<?php

namespace App\Jobs;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\IndexNowClient;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;

class SubmitPostUrlToIndexNowJob implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(
        public int $postId,
    ) {}

    public function handle(IndexNowClient $indexNowClient): void
    {
        if (! filled(config('indexnow.key'))) {
            return;
        }

        $post = Post::query()->find($this->postId);

        if ($post === null || $post->status !== PostStatus::Published) {
            return;
        }

        $indexNowClient->submitUrls([
            route('blog.show', $post, absolute: true),
        ]);
    }
}
