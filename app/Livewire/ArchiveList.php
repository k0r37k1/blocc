<?php

namespace App\Livewire;

use App\Models\Post;
use Carbon\Carbon;
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

    /**
     * SQL expression for calendar year of published_at (driver-specific).
     */
    private function yearExpression(): string
    {
        return match (Post::query()->getConnection()->getDriverName()) {
            'mysql', 'mariadb' => 'YEAR(published_at)',
            'pgsql' => 'EXTRACT(YEAR FROM published_at)',
            default => "strftime('%Y', published_at)",
        };
    }

    /**
     * SQL expression for calendar month (1–12) of published_at.
     */
    private function monthExpression(): string
    {
        return match (Post::query()->getConnection()->getDriverName()) {
            'mysql', 'mariadb' => 'MONTH(published_at)',
            'pgsql' => 'EXTRACT(MONTH FROM published_at)',
            default => "CAST(strftime('%m', published_at) AS INTEGER)",
        };
    }

    /** @return Collection<string, Collection<int, Post>> */
    #[Computed]
    public function postsByYear(): Collection
    {
        $query = Post::query()
            ->published()
            ->select(['title', 'slug', 'published_at'])
            ->orderByDesc('published_at');

        if (filled($this->year)) {
            $query->whereYear('published_at', (int) $this->year);
        }

        if (filled($this->month)) {
            $query->whereMonth('published_at', (int) $this->month);
        }

        return $query->get()->groupBy(fn (Post $post): int => $post->published_at->year);
    }

    /** @return Collection<int, array{year: string, count: int}> */
    #[Computed]
    public function availableYears(): Collection
    {
        $yearExpr = $this->yearExpression();

        return Post::query()
            ->published()
            ->selectRaw("{$yearExpr} as archive_year, COUNT(*) as c")
            ->groupByRaw($yearExpr)
            ->orderByDesc('archive_year')
            ->get()
            ->map(fn (object $row): array => [
                'year' => (string) $row->archive_year,
                'count' => (int) $row->c,
            ])
            ->values();
    }

    /** @return Collection<int, array{month: string, label: string, count: int}> */
    #[Computed]
    public function availableMonths(): Collection
    {
        if (blank($this->year)) {
            return collect();
        }

        $monthExpr = $this->monthExpression();

        $rows = Post::query()
            ->published()
            ->whereYear('published_at', (int) $this->year)
            ->selectRaw("{$monthExpr} as archive_month, COUNT(*) as c")
            ->groupByRaw($monthExpr)
            ->orderBy('archive_month')
            ->get();

        $locale = app()->getLocale();
        $year = (int) $this->year;

        return $rows->map(function (object $row) use ($locale, $year): array {
            $monthNum = (int) $row->archive_month;

            return [
                'month' => (string) $monthNum,
                'label' => Carbon::create($year, $monthNum, 1)
                    ->locale($locale)
                    ->translatedFormat('F'),
                'count' => (int) $row->c,
            ];
        })->values();
    }

    public function render(): View
    {
        return view('livewire.archive-list');
    }
}
