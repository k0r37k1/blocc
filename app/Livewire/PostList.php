<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Services\PostSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    private const FILTER_CHIPS_MIN_POSTS = 8;

    private const FILTER_CHIPS_MIN_CATEGORIES = 3;

    #[Url(except: '', history: true)]
    public string $search = '';

    #[Url(except: '', history: true)]
    public string $category = '';

    #[Url(except: '', history: true)]
    public string $tag = '';

    #[Url(except: 'newest', history: true)]
    public string $sort = 'newest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function toggleTag(string $slug): void
    {
        $this->tag = $this->tag === $slug ? '' : $slug;
        $this->resetPage();
    }

    public function selectCategory(string $slug): void
    {
        $this->category = $this->category === $slug ? '' : $slug;
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearCategory(): void
    {
        $this->category = '';
        $this->resetPage();
    }

    public function clearTag(): void
    {
        $this->tag = '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->tag = '';
        $this->resetPage();
    }

    #[Computed]
    public function showFilterChips(): bool
    {
        $postCount = Post::query()->published()->count();

        return $postCount >= self::FILTER_CHIPS_MIN_POSTS
            || $this->categories->count() >= self::FILTER_CHIPS_MIN_CATEGORIES;
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return filled(trim($this->search))
            || filled($this->category)
            || filled($this->tag);
    }

    #[Computed]
    public function activeCategory(): ?Category
    {
        if (blank($this->category)) {
            return null;
        }

        return $this->categories->firstWhere('slug', $this->category);
    }

    #[Computed]
    public function activeTag(): ?Tag
    {
        if (blank($this->tag)) {
            return null;
        }

        return $this->tags->firstWhere('slug', $this->tag);
    }

    /** @return LengthAwarePaginator<Post> */
    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        $term = trim($this->search);
        $search = app(PostSearch::class);
        $isFtsSearch = filled($term) && $search->ftsAvailable();

        return Post::query()
            ->published()
            ->with(['category', 'media'])
            ->withCount('approvedComments')
            ->when(filled($term), fn ($query) => $search->apply($query, $term))
            ->when(filled($this->category), fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $this->category)))
            ->when(filled($this->tag), fn ($query) => $query->whereHas('tags', fn ($query) => $query->where('slug', $this->tag)))
            ->when(
                ! $isFtsSearch,
                fn ($query) => $query->when(
                    $this->sort === 'oldest',
                    fn ($query) => $query->oldest('published_at'),
                    fn ($query) => $query->latest('published_at'),
                ),
            )
            ->paginate((int) Setting::get('posts_per_page', '10'))
            ->withQueryString();
    }

    /** @return Collection<int, Category> */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, Tag> */
    #[Computed]
    public function tags(): Collection
    {
        return Tag::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.post-list');
    }
}
