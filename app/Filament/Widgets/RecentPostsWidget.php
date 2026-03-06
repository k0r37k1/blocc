<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPostsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Recent Posts'))
            ->query(Post::query()->latest('updated_at')->limit(5))
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->limit(50)
                    ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
