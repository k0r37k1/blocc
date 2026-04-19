<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class IndexNowSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /** @var non-empty-string */
    private const string FAKE_INDEXNOW_KEY_FOR_TESTS = 'fake-indexnow-key-not-for-production';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
    }

    public function test_index_now_is_not_called_when_key_is_not_configured(): void
    {
        Config::set('indexnow.key', null);

        Http::fake();

        $post = Post::factory()->draft()->create([
            'slug' => 'index-now-off-'.uniqid(),
        ]);
        $post->update(['status' => PostStatus::Published]);

        Http::assertNothingSent();
    }

    public function test_index_now_is_called_when_post_is_published_from_draft(): void
    {
        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        $slug = 'index-now-draft-'.uniqid();
        $post = Post::factory()->draft()->create(['slug' => $slug]);
        $post->update(['status' => PostStatus::Published]);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($slug): bool {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->body(), true) ?? [];

            return $request->url() === 'https://api.indexnow.org/indexnow'
                && ($data['host'] ?? null) === 'localhost'
                && ($data['key'] ?? null) === self::FAKE_INDEXNOW_KEY_FOR_TESTS
                && ($data['keyLocation'] ?? null) === 'http://localhost/'.self::FAKE_INDEXNOW_KEY_FOR_TESTS.'.txt'
                && ($data['urlList'] ?? null) === ['http://localhost/blog/'.$slug];
        });
    }

    public function test_index_now_is_called_when_post_is_created_as_published(): void
    {
        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        $slug = 'index-now-new-'.uniqid();
        Post::factory()->published()->create(['slug' => $slug]);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($slug): bool {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->body(), true) ?? [];

            return ($data['urlList'] ?? null) === ['http://localhost/blog/'.$slug];
        });
    }

    public function test_index_now_is_not_called_again_when_published_post_is_updated(): void
    {
        Config::set('indexnow.key', null);

        $post = Post::factory()->published()->create([
            'slug' => 'index-now-stable-'.uniqid(),
            'title' => 'Original',
        ]);

        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        $post->update(['title' => 'Updated title']);

        Http::assertNothingSent();
    }

    public function test_custom_key_location_is_sent_when_configured(): void
    {
        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);
        Config::set('indexnow.key_location', 'https://cdn.example.com/verification-file.txt');

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        $slug = 'index-now-custom-kl-'.uniqid();
        $post = Post::factory()->draft()->create(['slug' => $slug]);
        $post->update(['status' => PostStatus::Published]);

        Http::assertSent(function ($request): bool {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->body(), true) ?? [];

            return ($data['keyLocation'] ?? null) === 'https://cdn.example.com/verification-file.txt';
        });
    }
}
