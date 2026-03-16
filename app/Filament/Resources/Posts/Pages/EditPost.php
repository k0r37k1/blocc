<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Post $record */
        $record = $this->record;

        return [
            Action::make('duplicate')
                ->label(__('Duplicate'))
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading(__('Duplicate post'))
                ->modalDescription(__('A copy will be created as a draft.'))
                ->action(function () use ($record): void {
                    $newPost = $record->replicate(['published_at']);
                    $newPost->title = $record->title.' ('.__('Copy').')';
                    $newPost->slug = Str::slug($newPost->title);
                    $newPost->status = PostStatus::Draft; /** @phpstan-ignore assign.propertyType */
                    $newPost->save();

                    $newPost->tags()->sync($record->tags->pluck('id'));

                    Notification::make()
                        ->title(__('Post duplicated'))
                        ->success()
                        ->send();

                    $this->redirect(PostResource::getUrl('edit', ['record' => $newPost]));
                }),
            Action::make('view-on-site')
                ->label(__('View on website'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => route('blog.show', $record->slug))
                ->openUrlInNewTab()
                ->color('gray')
                ->visible($record->is_published),
            DeleteAction::make(),
        ];
    }
}
