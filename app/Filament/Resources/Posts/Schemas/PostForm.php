<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
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
                ->hint(fn (?string $state): string => strlen($state ?? '').' / 255')
                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                    if (
                        ($get('status') !== PostStatus::Published->value) ||
                        (($get('slug') ?? '') === Str::slug($old ?? ''))
                    ) {
                        $set('slug', Str::slug($state ?? ''));
                    }
                }),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->rules(['alpha_dash'])
                ->helperText(fn (Get $get): string => $get('status') === PostStatus::Published->value
                    ? __('Slug is locked after publishing. Edit manually if needed.')
                    : __('Auto-generated from title. Will lock after publishing.')
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
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
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
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique('tags', 'slug'),
                ])
                ->native(false),
            RichEditor::make('body')
                ->required()
                ->toolbarButtons([
                    ['bold', 'italic', 'underline', 'strike', 'link'],
                    ['h2', 'h3'],
                    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                    ['table', 'horizontalRule', 'details'],
                    ['highlight', 'small', 'lead'],
                    ['attachFiles'],
                    ['undo', 'redo'],
                ])
                ->afterStateHydrated(fn ($component, $record) => $component->state($record?->body_raw ?? $record?->body))
                ->placeholder(__('Start writing...'))
                ->extraInputAttributes(['style' => 'min-height: 12rem'])
                ->columnSpanFull(),
            Textarea::make('excerpt')
                ->rows(3)
                ->maxLength(300)
                ->live(onBlur: true)
                ->hint(fn (?string $state): string => strlen($state ?? '').' / 300')
                ->helperText(__('Leave blank to auto-generate from the first ~160 characters of the body.'))
                ->columnSpanFull(),
            Placeholder::make('reading_time_display')
                ->label(__('Reading Time'))
                ->content(fn ($record): string => $record?->reading_time
                    ? "{$record->reading_time} ".__('min read')
                    : __('Calculated on save')),
            SpatieMediaLibraryFileUpload::make('featured_image')
                ->collection('featured-image')
                ->image()
                ->imageResizeMode('cover')
                ->imageResizeTargetWidth(1200)
                ->maxSize(5120)
                ->columnSpanFull()
                ->live(),
            TextInput::make('featured_image_alt')
                ->label(__('Featured Image Alt Text'))
                ->required(fn (Get $get): bool => filled($get('featured_image')))
                ->maxLength(255)
                ->helperText(__('Describe the image for accessibility. Required when a featured image is set.'))
                ->columnSpanFull(),
            Select::make('status')
                ->options(PostStatus::class)
                ->default(PostStatus::Draft)
                ->required()
                ->native(false),
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
