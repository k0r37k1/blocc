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
            Stat::make(__('Published Posts'), Post::published()->count())
                ->description(__('New Post'))
                ->descriptionIcon(Heroicon::DocumentCheck)
                ->color('success')
                ->url(PostResource::getUrl('create')),
            Stat::make(__('Drafts'), Post::draft()->count())
                ->description(__('In progress'))
                ->descriptionIcon(Heroicon::PencilSquare)
                ->color('warning'),
            Stat::make(__('Pages'), Page::count())
                ->description(__('Static pages'))
                ->descriptionIcon(Heroicon::DocumentDuplicate)
                ->color('info'),
            Stat::make(__('Last Published'), $latestPublished !== null ? $latestPublished->published_at->diffForHumans() : __('Never'))
                ->description(__('View latest'))
                ->descriptionIcon(Heroicon::Clock)
                ->color('gray')
                ->url($latestPublished !== null ? PostResource::getUrl('edit', ['record' => $latestPublished]) : null),
        ];
    }
}
