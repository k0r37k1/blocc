<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('newPost')
                ->label(__('New Post'))
                ->icon(Heroicon::PlusCircle)
                ->color('success')
                ->url(PostResource::getUrl('create')),

            Action::make('newPage')
                ->label(__('New Page'))
                ->icon(Heroicon::DocumentPlus)
                ->color('gray')
                ->url(PageResource::getUrl('create')),

            Action::make('backup')
                ->label(__('Backup'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading(__('Create Backup'))
                ->modalDescription(__('This will create a full backup of the database and files. This may take a moment.'))
                ->action(function (): void {
                    Artisan::call('backup:run', ['--only-db' => true]);

                    Notification::make()
                        ->title(__('Backup created'))
                        ->body(__('Database backup was created successfully.'))
                        ->success()
                        ->send();
                }),

            Action::make('resetData')
                ->label(__('Reset Data'))
                ->icon(Heroicon::ArrowPath)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('Reset all data'))
                ->modalDescription(__('This will delete ALL posts, pages, categories, tags, and media. Settings and your user account will be kept. This cannot be undone!'))
                ->modalSubmitActionLabel(__('Yes, delete everything'))
                ->action(function (): void {
                    Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

                    Notification::make()
                        ->title(__('Data reset'))
                        ->body(__('All data has been deleted and default seed data was restored.'))
                        ->warning()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
