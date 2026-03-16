<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Post;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->collection('featured-image')
                    ->conversion('thumbnail')
                    ->label('')
                    ->square()
                    ->imageSize(40)
                    ->toggleable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label(__('Published'))
                    ->onColor('success')
                    ->offColor('gray')
                    ->updateStateUsing(function (Post $record, bool $state): bool {
                        $record->update([
                            'status' => $state ? PostStatus::Published : PostStatus::Draft,
                            'published_at' => $state ? ($record->published_at ?? now()) : $record->published_at,
                        ]);

                        return $state;
                    }),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reading_time')
                    ->label(__('Read Time'))
                    ->formatStateUsing(fn (int $state): string => "{$state} min")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(PostStatus::class),
                SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label(__('Publish'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(fn ($record) => $record->update([
                                'status' => PostStatus::Published,
                                'published_at' => $record->published_at ?? now(),
                            ]));
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')
                        ->label(__('Unpublish'))
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(fn ($record) => $record->update([
                                'status' => PostStatus::Draft,
                            ]));
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
