<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
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

    /** @return LengthAwarePaginator<Post> */
    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        $term = mb_strtolower(trim($this->search), 'UTF-8');

        return Post::query()
            ->published()
            ->with(['category', 'media', 'author.media'])
            ->withCount('approvedComments')
            ->when(filled($term), function ($query) use ($term): void {
                $query->where(function ($query) use ($term): void {
                    $query->whereRaw('mb_lower(title) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('mb_lower(excerpt) LIKE ?', ["%{$term}%"]);
                });
            })
            ->when(filled($this->category), fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $this->category)))
            ->when(filled($this->tag), fn ($query) => $query->whereHas('tags', fn ($query) => $query->where('slug', $this->tag)))
            ->when($this->sort === 'oldest', fn ($query) => $query->oldest('published_at'), fn ($query) => $query->latest('published_at'))
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
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.post-list');
    }
}
