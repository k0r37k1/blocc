<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Services\PostContentProcessor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            // Process body through content pipeline (sanitize, highlight, anchors)
            if ($page->isDirty('body') && filled($page->body)) {
                $page->body_raw = $page->body;
                $page->body = app(PostContentProcessor::class)->process($page->body);
            }

            if ($page->status === PostStatus::Published && $page->published_at === null) {
                $page->published_at = now();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'body',
        'body_raw',
        'status',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Accessor for ToggleColumn compatibility.
     * Maps PostStatus enum to boolean.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === PostStatus::Published;
    }

    /**
     * Scope: only published pages.
     *
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published);
    }

    /**
     * Scope: only drafts.
     *
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Draft);
    }
}
