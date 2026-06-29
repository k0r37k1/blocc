<?php

namespace App\Jobs;

use App\Services\IndexNowClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;

class SubmitIndexNowUrls implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    /**
     * @param  list<string>  $urls
     */
    public function __construct(public array $urls) {}

    public function handle(IndexNowClient $client): void
    {
        $client->submitUrls($this->urls);
    }
}
