<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PostStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
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
                    ? 'Slug is locked after publishing. Edit manually if needed.'
                    : 'Auto-generated from title. Will lock after publishing.'
                ),
            RichEditor::make('body')
                ->required()
                ->toolbarButtons([
                    ['bold', 'italic', 'link'],
                    ['h2', 'h3'],
                    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                    ['table'],
                    ['undo', 'redo'],
                ])
                ->placeholder('Start writing...')
                ->extraInputAttributes(['style' => 'min-height: 12rem'])
                ->columnSpanFull(),
            Select::make('status')
                ->options(PostStatus::class)
                ->default(PostStatus::Draft)
                ->required()
                ->native(false),
            Placeholder::make('created_at')
                ->label('Created')
                ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '-'),
            Placeholder::make('updated_at')
                ->label('Last modified')
                ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-'),
        ]);
    }
}
