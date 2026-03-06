<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

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
        ];
    }
}
