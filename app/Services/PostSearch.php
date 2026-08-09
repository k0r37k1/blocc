<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PostSearch
{
    public function ftsAvailable(): bool
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return false;
        }

        try {
            return Schema::hasTable('posts_fts');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function apply(Builder $query, string $term): Builder
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        if ($this->ftsAvailable()) {
            $ids = $this->searchIds($term);

            if ($ids->isEmpty()) {
                return $query->whereRaw('0 = 1');
            }

            $orderCases = $ids
                ->values()
                ->map(fn (int|string $id, int $index): string => "WHEN {$id} THEN {$index}")
                ->implode(' ');

            return $query
                ->whereIn($query->qualifyColumn('id'), $ids->all())
                ->orderByRaw("CASE {$query->qualifyColumn('id')} {$orderCases} END");
        }

        $needle = mb_strtolower($term, 'UTF-8');

        return $query->where(function (Builder $query) use ($needle): void {
            $query->whereRaw('mb_lower(title) LIKE ?', ["%{$needle}%"])
                ->orWhereRaw('mb_lower(excerpt) LIKE ?', ["%{$needle}%"])
                ->orWhereRaw('mb_lower(body_raw) LIKE ?', ["%{$needle}%"]);
        });
    }

    /**
     * @return Collection<int, int|string>
     */
    public function searchIds(string $term): Collection
    {
        $match = $this->buildMatchQuery($term);

        if ($match === '') {
            return collect();
        }

        try {
            return DB::table('posts_fts')
                ->select('rowid')
                ->whereRaw('posts_fts MATCH ?', [$match])
                ->orderByRaw('bm25(posts_fts)')
                ->limit(200)
                ->pluck('rowid');
        } catch (Throwable) {
            return collect();
        }
    }

    public function sync(Post $post): void
    {
        if (! $this->ftsAvailable()) {
            return;
        }

        $this->remove($post);

        DB::table('posts_fts')->insert([
            'rowid' => $post->getKey(),
            'title' => (string) $post->title,
            'excerpt' => (string) ($post->excerpt ?? ''),
            'body' => strip_tags((string) ($post->body_raw ?? $post->body ?? '')),
        ]);
    }

    public function remove(Post $post): void
    {
        if (! $this->ftsAvailable()) {
            return;
        }

        DB::table('posts_fts')->where('rowid', $post->getKey())->delete();
    }

    public function rebuild(): void
    {
        if (! $this->ftsAvailable()) {
            return;
        }

        DB::table('posts_fts')->delete();

        Post::query()
            ->select(['id', 'title', 'excerpt', 'body', 'body_raw'])
            ->orderBy('id')
            ->chunkById(100, function (Collection $posts): void {
                foreach ($posts as $post) {
                    $this->sync($post);
                }
            });
    }

    private function buildMatchQuery(string $term): string
    {
        $tokens = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($tokens)
            ->map(function (string $token): ?string {
                $cleaned = preg_replace('/[^\p{L}\p{N}_-]+/u', '', $token) ?? '';

                if ($cleaned === '') {
                    return null;
                }

                $escaped = str_replace('"', '""', $cleaned);

                return "\"{$escaped}\"*";
            })
            ->filter()
            ->implode(' ');
    }
}
