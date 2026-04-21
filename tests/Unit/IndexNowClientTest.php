<?php

namespace Tests\Unit;

use App\Services\IndexNowClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndexNowClientTest extends TestCase
{
    /** @var non-empty-string */
    private const string FAKE_KEY = 'fake-indexnow-key-not-for-production';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('indexnow.key', self::FAKE_KEY);
    }

    public function test_host_and_default_key_location_match_first_submitted_url_not_app_url(): void
    {
        Config::set('app.url', 'https://configured.example.com');
        Config::set('indexnow.key_location', null);

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        app(IndexNowClient::class)->submitUrls([
            'https://public.example.org/blog/my-post',
        ]);

        Http::assertSent(function ($request): bool {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->body(), true) ?? [];

            return $request->url() === 'https://api.indexnow.org/indexnow'
                && ($data['host'] ?? null) === 'public.example.org'
                && ($data['key'] ?? null) === self::FAKE_KEY
                && ($data['keyLocation'] ?? null) === 'https://public.example.org/'.self::FAKE_KEY.'.txt'
                && ($data['urlList'] ?? null) === ['https://public.example.org/blog/my-post'];
        });
    }

    public function test_http_scheme_is_used_for_default_key_location_when_submitted_url_has_no_scheme(): void
    {
        Config::set('indexnow.key_location', null);

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        app(IndexNowClient::class)->submitUrls([
            '//cdn.example/blog/x',
        ]);

        Http::assertSent(function ($request): bool {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->body(), true) ?? [];

            return ($data['host'] ?? null) === 'cdn.example'
                && ($data['keyLocation'] ?? null) === 'https://cdn.example/'.self::FAKE_KEY.'.txt';
        });
    }
}
