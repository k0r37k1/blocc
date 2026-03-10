<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Comment::query()->with(['post', 'parent']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('is_approved')
                    ->label(__('Status'))
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::Clock)
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),
                TextColumn::make('nickname')
                    ->label(__('Nickname'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content')
                    ->label(__('Comment'))
                    ->limit(80)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('post.title')
                    ->label(__('Post'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('parent.nickname')
                    ->label(__('Reply to'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->label(__('IP'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_author')
                    ->label(__('Author'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label(__('Status'))
                    ->trueLabel(__('Approved'))
                    ->falseLabel(__('Pending'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_approved', true),
                        false: fn (Builder $query) => $query->where('is_approved', false),
                    ),
                SelectFilter::make('post_id')
                    ->label(__('Post'))
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon(Heroicon::Check)
                    ->color('success')
                    ->visible(fn (Comment $record): bool => ! $record->is_approved)
                    ->action(function (Comment $record): void {
                        $record->update(['is_approved' => true]);

                        Notification::make()
                            ->title(__('Comment approved'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label(__('Approve'))
                        ->icon(Heroicon::Check)
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => Comment::whereIn('id', $records->pluck('id'))->update(['is_approved' => true])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
