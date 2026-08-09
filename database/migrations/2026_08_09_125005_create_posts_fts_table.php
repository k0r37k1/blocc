<?php

use App\Models\Post;
use App\Services\PostSearch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('DROP TABLE IF EXISTS posts_fts');

        DB::statement(<<<'SQL'
            CREATE VIRTUAL TABLE posts_fts USING fts5(
                title,
                excerpt,
                body,
                tokenize = 'unicode61 remove_diacritics 2'
            )
            SQL);

        $search = app(PostSearch::class);

        Post::query()
            ->select(['id', 'title', 'excerpt', 'body', 'body_raw'])
            ->orderBy('id')
            ->chunkById(100, function ($posts) use ($search): void {
                foreach ($posts as $post) {
                    $search->sync($post);
                }
            });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('DROP TABLE IF EXISTS posts_fts');
    }
};
