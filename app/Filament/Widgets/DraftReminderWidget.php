<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class DraftReminderWidget extends Widget
{
    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.draft-reminder';

    protected int|string|array $columnSpan = 'full';

    public function getDraftCount(): int
    {
        return Post::draft()->count();
    }

    public function getDrafts(): Collection
    {
        return Post::draft()->latest('updated_at')->limit(5)->get(['id', 'title', 'slug', 'updated_at']);
    }

    public function getEditUrl(Post $post): string
    {
        return PostResource::getUrl('edit', ['record' => $post]);
    }
}
