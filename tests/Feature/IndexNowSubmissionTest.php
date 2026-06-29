<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Jobs\SubmitIndexNowUrls;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
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

    public function test_index_now_job_is_not_dispatched_when_key_is_not_configured(): void
    {
        Config::set('indexnow.key', null);

        Queue::fake();

        $post = Post::factory()->draft()->create([
            'slug' => 'index-now-off-'.uniqid(),
        ]);
        $post->update(['status' => PostStatus::Published]);

        Queue::assertNothingPushed();
    }

    public function test_index_now_job_is_dispatched_when_post_is_published_from_draft(): void
    {
        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);

        Queue::fake();

        $slug = 'index-now-draft-'.uniqid();
        $post = Post::factory()->draft()->create(['slug' => $slug]);
        $post->update(['status' => PostStatus::Published]);

        Queue::assertPushed(SubmitIndexNowUrls::class, function (SubmitIndexNowUrls $job) use ($slug): bool {
            return $job->urls === ['http://localhost/blog/'.$slug];
        });
    }

    public function test_index_now_job_is_dispatched_when_post_is_created_as_published(): void
    {
        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);

        Queue::fake();

        $slug = 'index-now-new-'.uniqid();
        Post::factory()->published()->create(['slug' => $slug]);

        Queue::assertPushed(SubmitIndexNowUrls::class, function (SubmitIndexNowUrls $job) use ($slug): bool {
            return $job->urls === ['http://localhost/blog/'.$slug];
        });
    }

    public function test_index_now_job_is_dispatched_when_published_post_content_is_updated(): void
    {
        Config::set('indexnow.key', null);

        $slug = 'index-now-update-'.uniqid();
        $post = Post::factory()->published()->create([
            'slug' => $slug,
            'title' => 'Original',
        ]);

        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);

        Queue::fake();

        $post->update(['title' => 'Updated title']);

        Queue::assertPushed(SubmitIndexNowUrls::class, function (SubmitIndexNowUrls $job) use ($slug): bool {
            return $job->urls === ['http://localhost/blog/'.$slug];
        });
    }

    public function test_submit_index_now_job_calls_index_now_client(): void
    {
        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        $slug = 'index-now-job-'.uniqid();
        $job = new SubmitIndexNowUrls(['http://localhost/blog/'.$slug]);
        $this->app->call([$job, 'handle']);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($slug): bool {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->body(), true) ?? [];

            return $request->url() === 'https://api.indexnow.org/indexnow'
                && ($data['urlList'] ?? null) === ['http://localhost/blog/'.$slug];
        });
    }

    public function test_custom_key_location_is_sent_when_configured(): void
    {
        Config::set('indexnow.key', self::FAKE_INDEXNOW_KEY_FOR_TESTS);
        Config::set('indexnow.key_location', 'https://cdn.example.com/verification-file.txt');

        Http::fake([
            'https://api.indexnow.org/indexnow' => Http::response('', 200),
        ]);

        $slug = 'index-now-custom-kl-'.uniqid();
        $job = new SubmitIndexNowUrls(['http://localhost/blog/'.$slug]);
        $this->app->call([$job, 'handle']);

        Http::assertSent(function ($request): bool {
            /** @var array<string, mixed> $data */
            $data = json_decode($request->body(), true) ?? [];

            return ($data['keyLocation'] ?? null) === 'https://cdn.example.com/verification-file.txt';
        });
    }
}
