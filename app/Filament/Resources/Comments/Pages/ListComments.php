<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Resources\Pages\ListRecords;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    public function mount(): void
    {
        parent::mount();

        if (request()->boolean('pending')) {
            $this->tableFilters = [
                'is_approved' => [
                    'value' => false,
                ],
            ];
        }
    }
}
