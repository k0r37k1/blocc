<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\BrevoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BrevoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_double_opt_in_posts_to_brevo_api(): void
    {
        config(['brevo.api_key' => 'test-api-key']);

        Setting::setMany([
            'brevo_list_id' => '42',
            'brevo_doi_template_id' => '7',
        ]);

        Http::fake([
            'api.brevo.com/*' => Http::response(['message' => 'ok'], 201),
        ]);

        $response = app(BrevoService::class)->sendDoubleOptIn('reader@example.com');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.brevo.com/v3/contacts/doubleOptinConfirmation'
                && $request['email'] === 'reader@example.com'
                && $request['includeListIds'] === [42]
                && $request['templateId'] === 7;
        });

        $this->assertTrue($response->successful());
        $this->assertTrue(app(BrevoService::class)->isSuccessfulResponse($response));
    }

    public function test_treats_400_response_as_successful(): void
    {
        config(['brevo.api_key' => 'test-api-key']);

        Setting::setMany([
            'brevo_list_id' => '42',
            'brevo_doi_template_id' => '7',
        ]);

        Http::fake([
            'api.brevo.com/*' => Http::response(['message' => 'exists'], 400),
        ]);

        $response = app(BrevoService::class)->sendDoubleOptIn('reader@example.com');

        $this->assertFalse($response->successful());
        $this->assertTrue(app(BrevoService::class)->isSuccessfulResponse($response));
    }
}
