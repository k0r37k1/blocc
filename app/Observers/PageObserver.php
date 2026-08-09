<?php

namespace App\Observers;

use App\Models\Page;
use App\Services\PostCache;

class PageObserver
{
    public function saved(Page $page): void
    {
        PostCache::clearResponseCache();
    }

    public function deleted(Page $page): void
    {
        PostCache::clearResponseCache();
    }
}
