<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Page;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $latestPublished = Post::published()->latest('published_at')->first();

        return [
            Stat::make('Published Posts', Post::published()->count())
                ->description('New Post')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success')
                ->url(PostResource::getUrl('create')),
            Stat::make('Drafts', Post::draft()->count())
                ->description('In progress')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),
            Stat::make('Pages', Page::count())
                ->description('Static pages')
                ->descriptionIcon('heroicon-m-document-duplicate')
                ->color('info'),
            Stat::make('Last Published', $latestPublished?->published_at?->diffForHumans() ?? 'Never')
                ->description('View latest')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray')
                ->url($latestPublished ? PostResource::getUrl('edit', ['record' => $latestPublished]) : null),
        ];
    }
}
