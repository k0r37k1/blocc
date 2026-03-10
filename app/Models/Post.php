<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Services\PostContentProcessor;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property \Illuminate\Support\Carbon $published_at
 */
class Post extends Model implements HasMedia, HasRichContent
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use InteractsWithRichContent;

    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if ($post->user_id === null && Auth::check()) {
                /** @var int<0, max> $id */
                $id = Auth::id();
                $post->user_id = $id;
            }
        });

        static::saving(function (Post $post): void {
            // Process body through content pipeline (sanitize, highlight, anchors)
            if ($post->isDirty('body') && filled($post->body)) {
                $post->body_raw = $post->body;
                $post->body = app(PostContentProcessor::class)->process($post->body);
            }

            if ($post->status === PostStatus::Published && $post->published_at === null) {
                $post->published_at = now();
            }

            // Calculate reading time from raw body (200 wpm, minimum 1 minute)
            $sourceBody = $post->body_raw ?? $post->body ?? '';
            $wordCount = str_word_count(strip_tags($sourceBody));
            /** @var int<0, max> $readingTime */
            $readingTime = (int) max(1, ceil($wordCount / 200));
            $post->reading_time = $readingTime;

            // Auto-generate excerpt if not manually set
            if (blank($post->excerpt) && filled($post->body_raw ?? $post->body)) {
                $plainText = strip_tags($post->body_raw ?? $post->body);
                $post->excerpt = Str::limit($plainText, 160);
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
        'category_id',
        'user_id',
        'excerpt',
        'reading_time',
        'featured_image_alt',
        'comments_enabled',
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
            'reading_time' => 'integer',
            'comments_enabled' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return HasMany<Comment, $this> */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('is_approved', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured-image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 400, 300)
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->fit(Fit::Contain, 800, 600)
            ->format('webp')
            ->nonQueued();
    }

    public function setUpRichContent(): void
    {
        $this->registerRichContent('body')
            ->fileAttachmentProvider(
                SpatieMediaLibraryFileAttachmentProvider::make()
                    ->collection('body-attachments'),
            );
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
     * Scope: only published posts.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published);
    }

    /**
     * Scope: only drafts.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Draft);
    }
}
