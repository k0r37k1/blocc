<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Filament\RichEditor\BodyToolbar;
use App\Services\SiteMedia;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->partiallyRenderAfterStateUpdated()
                ->hint(fn (?string $state): string => strlen($state ?? '').' / 255')
                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                    $status = $get('status');
                    $slugLocked = in_array($status, [PostStatus::Published->value, PostStatus::Scheduled->value], true);

                    if (
                        ! $slugLocked ||
                        (($get('slug') ?? '') === Str::slug($old ?? '', '-', 'de'))
                    ) {
                        $set('slug', Str::slug($state ?? '', '-', 'de'));
                    }
                }),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->rules(['alpha_dash'])
                ->helperText(fn (Get $get): string => in_array($get('status'), [PostStatus::Published->value, PostStatus::Scheduled->value], true)
                    ? __('Slug is locked after publishing or scheduling. Edit manually if needed.')
                    : __('Auto-generated from title. Will lock after publishing or scheduling.')
                ),
            Select::make('category_id')
                ->relationship(name: 'category', titleAttribute: 'name')
                ->required()
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->partiallyRenderAfterStateUpdated()
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? '', '-', 'de'))),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique('categories', 'slug'),
                ])
                ->native(false),
            Select::make('tags')
                ->multiple()
                ->relationship(titleAttribute: 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->partiallyRenderAfterStateUpdated()
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? '', '-', 'de'))),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique('tags', 'slug'),
                ])
                ->native(false),
            Textarea::make('excerpt')
                ->rows(3)
                ->maxLength(160)
                ->live(onBlur: true)
                ->partiallyRenderAfterStateUpdated()
                ->hint(fn (?string $state): string => strlen($state ?? '').' / 160')
                ->helperText(__('Leave blank to auto-generate from the body (max. 160 characters, adds … when truncated).'))
                ->columnSpanFull(),
            // New toolbar features: keep `config/purify.php` + `App\Purify\*Definition` in sync with TipTap output.
            RichEditor::make('body')
                ->required()
                ->toolbarButtons(BodyToolbar::buttons())
                ->afterStateHydrated(fn ($component, $record) => $component->state($record?->body_raw ?? $record?->body))
                ->placeholder(__('Start writing...'))
                ->extraInputAttributes(['style' => 'min-height: 12rem'])
                ->columnSpanFull(),
            Placeholder::make('reading_time_display')
                ->label(__('Reading Time'))
                ->content(fn ($record): string => $record?->reading_time
                    ? "{$record->reading_time} ".__('min read')
                    : __('Calculated on save')),
            Hidden::make('site_media_id')
                ->live(),
            View::make('filament.forms.components.site-media-selection')
                ->visible(fn (Get $get): bool => filled($get('site_media_id')))
                ->viewData(fn (Get $get): array => [
                    'media' => filled($get('site_media_id'))
                        ? app(SiteMedia::class)->find((int) $get('site_media_id'))
                        : null,
                ]),
            SpatieMediaLibraryFileUpload::make('featured_image')
                ->collection('featured-image')
                ->image()
                ->maxSize(5120)
                ->columnSpanFull()
                ->live()
                ->preserveFilenames()
                ->visible(fn (Get $get): bool => blank($get('site_media_id')))
                ->belowContent(
                    Action::make('chooseSiteMedia')
                        ->label(__('Choose from media'))
                        ->link()
                        ->icon(Heroicon::OutlinedPhoto)
                        ->modalHeading(__('Choose featured image'))
                        ->modalSubmitActionLabel(__('Use selected image'))
                        ->modalWidth(Width::ThreeExtraLarge)
                        ->extraModalWindowAttributes([
                            'class' => 'mx-3 w-full max-w-[min(48rem,calc(100vw-1.5rem))] sm:mx-auto',
                        ])
                        ->schema(function (): array {
                            $items = app(SiteMedia::class)->items();

                            if ($items->isEmpty()) {
                                return [
                                    View::make('filament.forms.components.site-media-picker')
                                        ->viewData(fn (): array => [
                                            'items' => $items,
                                        ]),
                                ];
                            }

                            return [
                                Hidden::make('media_id')
                                    ->live()
                                    ->required(),
                                View::make('filament.forms.components.site-media-picker')
                                    ->viewData(fn (): array => [
                                        'items' => $items,
                                    ]),
                            ];
                        })
                        ->modalSubmitAction(fn (): bool => app(SiteMedia::class)->items()->isNotEmpty())
                        ->action(function (array $data, Set $set): void {
                            $set('site_media_id', $data['media_id']);
                        }),
                )
                ->afterStateUpdated(function ($state, Set $set): void {
                    if (filled($state)) {
                        $set('site_media_id', null);
                    }
                }),
            Checkbox::make('use_placeholder_image')
                ->label(__('Use random placeholder image if no image uploaded'))
                ->default(false)
                ->visible(fn (Get $get, string $operation): bool => $operation === 'create'
                    && blank($get('site_media_id')))
                ->columnSpanFull(),
            TextInput::make('featured_image_alt')
                ->label(__('Featured Image Alt Text'))
                ->required(fn (Get $get): bool => filled($get('featured_image'))
                    || filled($get('site_media_id')))
                ->maxLength(255)
                ->helperText(__('Describe the image for accessibility. Required when a featured image is set.'))
                ->columnSpanFull(),
            Toggle::make('comments_enabled')
                ->label(__('Allow Comments'))
                ->helperText(__('Disable to hide the comment section on this post.'))
                ->default(true),
            Toggle::make('toc_enabled')
                ->label(__('Show Table of Contents'))
                ->helperText(__('Disable to hide the table of contents on this post.'))
                ->default(true),
            Select::make('status')
                ->options(PostStatus::class)
                ->default(PostStatus::Draft)
                ->required()
                ->live()
                ->native(false),
            DateTimePicker::make('published_at')
                ->label(__('Publish date'))
                ->visible(fn (Get $get): bool => in_array($get('status'), [PostStatus::Published->value, PostStatus::Scheduled->value], true))
                ->required(fn (Get $get): bool => $get('status') === PostStatus::Scheduled->value)
                ->minDate(fn (Get $get): ?\Illuminate\Support\Carbon => $get('status') === PostStatus::Scheduled->value ? now() : null)
                ->helperText(fn (Get $get): string => $get('status') === PostStatus::Scheduled->value
                    ? __('Required for scheduled posts. The post will go live automatically at this time.')
                    : __('Optional. Leave empty to publish immediately, or pick a future date to schedule.')
                ),
            Placeholder::make('created_at')
                ->label(__('Created'))
                ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '-')
                ->visibleOn('edit'),
            Placeholder::make('updated_at')
                ->label(__('Last modified'))
                ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-')
                ->visibleOn('edit'),
        ]);
    }
}
