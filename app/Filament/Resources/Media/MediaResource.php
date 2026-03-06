<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ListMedia;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $pluralModelLabel = 'Media';

    protected static ?string $modelLabel = 'Media';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Content');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview')
                    ->label('')
                    ->getStateUsing(fn (Media $record): string => $record->getUrl())
                    ->size(48),
                TextColumn::make('file_name')
                    ->label(__('Filename'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('collection_name')
                    ->label(__('Collection'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('model_type')
                    ->label(__('Used by'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->sortable(),
                TextColumn::make('size')
                    ->label(__('Size'))
                    ->formatStateUsing(fn (int $state): string => number_format($state / 1024, 0).' KB')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Uploaded'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->iconButton()
                    ->url(fn (Media $record): string => $record->getUrl())
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
        ];
    }
}
