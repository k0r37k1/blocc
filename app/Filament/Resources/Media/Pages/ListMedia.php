<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\SpatieLaravelMediaLibraryPlugin\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Support\Icons\Heroicon;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label(__('Upload'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->schema([
                    SpatieMediaLibraryFileUpload::make('files')
                        ->label(__('Files'))
                        ->collection('uploads')
                        ->model(Site::instance())
                        ->multiple()
                        ->maxSize(10240)
                        ->panelLayout('grid')
                        ->acceptedFileTypes(['image/*', 'application/pdf', 'video/*']),
                ])
                ->action(function (): void {
                    Notification::make()
                        ->title(__('Files uploaded'))
                        ->success()
                        ->send();
                }),

            Action::make('siteAssets')
                ->label(__('Site Assets'))
                ->icon(Heroicon::OutlinedPhoto)
                ->color('gray')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('site_assets')
                        ->label(__('Logo & Favicon'))
                        ->helperText(__('Upload logo_light, logo_dark, favicon — filename determines usage.'))
                        ->collection('site_assets')
                        ->model(Site::instance())
                        ->multiple()
                        ->image()
                        ->maxSize(1024)
                        ->panelLayout('grid')
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon']),
                ])
                ->action(function (): void {
                    Notification::make()
                        ->title(__('Site assets saved'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
