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

        $created = false;

        foreach ([
            "CREATE VIRTUAL TABLE posts_fts USING fts5(
                title,
                excerpt,
                body,
                tokenize = 'unicode61 remove_diacritics 2'
            )",
            "CREATE VIRTUAL TABLE posts_fts USING fts5(
                title,
                excerpt,
                body,
                tokenize = 'unicode61 remove_diacritics 1'
            )",
            "CREATE VIRTUAL TABLE posts_fts USING fts5(
                title,
                excerpt,
                body,
                tokenize = 'unicode61'
            )",
        ] as $sql) {
            try {
                DB::statement($sql);
                $created = true;
                break;
            } catch (Throwable) {
                DB::statement('DROP TABLE IF EXISTS posts_fts');
            }
        }

        if (! $created) {
            throw new RuntimeException('Unable to create posts_fts virtual table on this SQLite build.');
        }

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
