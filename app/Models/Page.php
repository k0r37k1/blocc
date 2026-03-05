<?php

namespace App\Models;

use App\Enums\PostStatus;
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
     * Resolve route binding scoped to published pages only.
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->published()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
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
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published);
    }

    /**
     * Scope: only drafts.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Draft);
    }
}
