<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Published')
                    ->onColor('success')
                    ->offColor('gray')
                    ->updateStateUsing(function ($record, $state) {
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
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(PostStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish')
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
                        ->label('Unpublish')
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
