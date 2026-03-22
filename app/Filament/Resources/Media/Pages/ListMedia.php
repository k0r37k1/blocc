<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('siteAssets')
                ->label(__('Site Assets'))
                ->icon(Heroicon::OutlinedPhoto)
                ->color('gray')
                ->schema([
                    FileUpload::make('site_assets')
                        ->label(__('Logo & Favicon'))
                        ->helperText(__('Upload logo_light, logo_dark, favicon — filename determines usage.'))
                        ->multiple()
                        ->disk('public')
                        ->directory('site-assets-tmp')
                        ->image()
                        ->maxSize(1024)
                        ->panelLayout('grid')
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon']),
                ])
                ->action(function (array $data): void {
                    $site = Site::instance();

                    foreach ($data['site_assets'] ?? [] as $path) {
                        $basename = pathinfo($path, PATHINFO_FILENAME);

                        $site->getMedia('site_assets')
                            ->filter(fn ($m) => pathinfo($m->file_name, PATHINFO_FILENAME) === $basename)
                            ->each->delete();

                        $site->addMediaFromDisk($path, 'public')
                            ->toMediaCollection('site_assets');
                    }

                    Notification::make()
                        ->title(__('Site assets saved'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
