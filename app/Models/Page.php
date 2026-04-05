<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks\CalloutRichContentBlock;
use App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks\VideoEmbedRichContentBlock;
use App\Services\PostContentProcessor;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Filament\Forms\Components\RichEditor\TextColor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia, HasRichContent
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use InteractsWithRichContent;

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            // Process body through content pipeline (sanitize, highlight, anchors)
            if ($page->isDirty('body') && filled($page->body)) {
                $page->body_raw = $page->body;
                $page->body = app(PostContentProcessor::class)->process($page->body, $page);
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
        'sort_order',
        'show_in_nav',
        'show_in_footer',
        'is_system',
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
            'is_system' => 'boolean',
        ];
    }

    public function setUpRichContent(): void
    {
        $this->registerRichContent('body')
            ->textColors([
                ...TextColor::getDefaults(),
            ])
            ->customBlocks([
                VideoEmbedRichContentBlock::class,
                CalloutRichContentBlock::class,
            ])
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
