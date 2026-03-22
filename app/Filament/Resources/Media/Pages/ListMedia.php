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
                ->modalWidth('2xl')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('files')
                        ->hiddenLabel()
                        ->helperText(__('For logo and favicon, use the filenames logo_light, logo_dark, or favicon.'))
                        ->collection('uploads')
                        ->model(Site::instance())
                        ->multiple()
                        ->image()
                        ->imagePreviewHeight('200')
                        ->maxSize(10240)
                        ->panelLayout('grid')
                        ->panelAspectRatio('2:1')
                        ->reorderable(),
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
