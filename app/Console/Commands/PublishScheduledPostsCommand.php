<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish posts whose scheduled publish date has passed';

    public function handle(): int
    {
        $publishedCount = 0;

        Post::query()
            ->scheduled()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($posts) use (&$publishedCount): void {
                foreach ($posts as $post) {
                    $post->update(['status' => PostStatus::Published]);
                    $publishedCount++;
                }
            });

        if ($publishedCount === 0) {
            $this->info('No scheduled posts to publish.');

            return self::SUCCESS;
        }

        $this->info("Published {$publishedCount} scheduled post(s).");

        return self::SUCCESS;
    }
}
