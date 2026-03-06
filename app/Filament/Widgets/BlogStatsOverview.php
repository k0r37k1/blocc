<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Page;
use App\Models\Post;
use Filament\Support\Icons\Heroicon;
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
                ->descriptionIcon(Heroicon::DocumentCheck)
                ->color('success')
                ->url(PostResource::getUrl('create')),
            Stat::make('Drafts', Post::draft()->count())
                ->description('In progress')
                ->descriptionIcon(Heroicon::PencilSquare)
                ->color('warning'),
            Stat::make('Pages', Page::count())
                ->description('Static pages')
                ->descriptionIcon(Heroicon::DocumentDuplicate)
                ->color('info'),
            Stat::make('Last Published', $latestPublished !== null ? $latestPublished->published_at->diffForHumans() : 'Never')
                ->description('View latest')
                ->descriptionIcon(Heroicon::Clock)
                ->color('gray')
                ->url($latestPublished !== null ? PostResource::getUrl('edit', ['record' => $latestPublished]) : null),
        ];
    }
}
