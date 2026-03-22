<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
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
                        ->helperText(__('For logo and favicon, use the filenames logo_light, logo_dark, or favicon.'))
                        ->collection('uploads')
                        ->model(Site::instance())
                        ->multiple()
                        ->maxSize(10240)
                        ->panelLayout('grid')
                        ->reorderable()
                        ->acceptedFileTypes(['image/*']),
                ])
                ->action(function (): void {
                    Notification::make()
                        ->title(__('Files uploaded'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
