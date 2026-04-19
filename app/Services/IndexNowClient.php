<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class IndexNowClient
{
    /**
     * @param  list<string>  $urls
     */
    public function submitUrls(array $urls): void
    {
        if ($urls === []) {
            return;
        }

        $key = config('indexnow.key');
        if (! is_string($key) || $key === '') {
            return;
        }

        $appUrl = (string) config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            Log::warning('IndexNow skipped: app.url has no host.', [
                'app_url' => $appUrl,
            ]);

            return;
        }

        $keyLocation = config('indexnow.key_location');
        if (! is_string($keyLocation) || $keyLocation === '') {
            $keyLocation = rtrim($appUrl, '/').'/'.$key.'.txt';
        }

        $endpoint = (string) config('indexnow.endpoint', 'https://api.indexnow.org/indexnow');

        $response = Http::timeout(15)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, [
                'host' => $host,
                'key' => $key,
                'keyLocation' => $keyLocation,
                'urlList' => array_values($urls),
            ]);

        if (! $response->successful()) {
            Log::warning('IndexNow submission failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
