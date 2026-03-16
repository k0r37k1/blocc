<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Page $record */
        $record = $this->record;

        return [
            Action::make('view-on-site')
                ->label(__('View on website'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => route('page.show', $record->slug))
                ->openUrlInNewTab()
                ->color('gray')
                ->visible($record->is_published),
            DeleteAction::make(),
        ];
    }
}
