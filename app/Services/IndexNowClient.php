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
        $urls = array_values(array_filter($urls, fn (string $url): bool => $url !== ''));

        if ($urls === []) {
            return;
        }

        $key = config('indexnow.key');
        if (! is_string($key) || $key === '') {
            return;
        }

        $firstUrl = $urls[0];
        $host = parse_url($firstUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            Log::warning('IndexNow skipped: submitted URL has no host.', [
                'url' => $firstUrl,
            ]);

            return;
        }

        $scheme = parse_url($firstUrl, PHP_URL_SCHEME);
        if (! is_string($scheme) || $scheme === '') {
            $scheme = 'https';
        }

        $keyLocation = config('indexnow.key_location');
        if (! is_string($keyLocation) || $keyLocation === '') {
            $keyLocation = $scheme.'://'.$host.'/'.$key.'.txt';
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
