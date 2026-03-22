<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class ArchiveList extends Component
{
    #[Url(except: '', history: true)]
    public string $year = '';

    #[Url(except: '', history: true)]
    public string $month = '';

    public function updatedYear(): void
    {
        $this->month = '';
    }

    /** @return Collection<string, Collection<int, Post>> */
    #[Computed]
    public function postsByYear(): Collection
    {
        return Post::query()
            ->published()
            ->latest('published_at')
            ->get(['title', 'slug', 'published_at'])
            ->when(filled($this->year), fn ($posts) => $posts->filter(
                fn (Post $post): bool => (string) $post->published_at->year === $this->year
            ))
            ->when(filled($this->month), fn ($posts) => $posts->filter(
                fn (Post $post): bool => (string) $post->published_at->month === $this->month
            ))
            ->groupBy(fn (Post $post): int => $post->published_at->year);
    }

    /** @return Collection<int, array{year: string, count: int}> */
    #[Computed]
    public function availableYears(): Collection
    {
        return Post::query()
            ->published()
            ->get(['published_at'])
            ->groupBy(fn (Post $post): string => (string) $post->published_at->year)
            ->map(fn (Collection $posts, string $year): array => [
                'year' => $year,
                'count' => $posts->count(),
            ])
            ->sortKeysDesc()
            ->values();
    }

    /** @return Collection<int, array{month: string, label: string, count: int}> */
    #[Computed]
    public function availableMonths(): Collection
    {
        if (blank($this->year)) {
            return collect();
        }

        return Post::query()
            ->published()
            ->get(['published_at'])
            ->filter(fn (Post $post): bool => (string) $post->published_at->year === $this->year)
            ->groupBy(fn (Post $post): string => (string) $post->published_at->month)
            ->map(fn (Collection $posts, string $month): array => [
                'month' => $month,
                'label' => $posts->first()->published_at->translatedFormat('F'),
                'count' => $posts->count(),
            ])
            ->sortKeys()
            ->values();
    }

    public function render(): View
    {
        return view('livewire.archive-list');
    }
}
