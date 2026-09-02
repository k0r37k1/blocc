<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Site;
use App\Services\SiteMedia;
use Filament\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    public function mount(): void
    {
        parent::mount();

        app(SiteMedia::class)->syncLibraryFromPostsAndLegacy();
    }

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
                        ->helperText(__('Shared site media: logos, favicon, and images for posts. Use filenames logo_light, logo_dark, or favicon for branding assets.'))
                        ->collection(SiteMedia::COLLECTION)
                        ->model(Site::instance())
                        ->multiple()
                        ->preserveFilenames()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml'])
                        ->imagePreviewHeight('200')
                        ->maxSize(10240)
                        ->panelLayout('grid')
                        ->panelAspectRatio('2:1')
                        ->reorderable(),
                ])
                ->action(function (SiteMedia $siteMedia): void {
                    $siteMedia->stampMissingContentHashes();

                    Notification::make()
                        ->title(__('Files uploaded'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
