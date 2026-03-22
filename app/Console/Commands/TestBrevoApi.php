<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestBrevoApi extends Command
{
    protected $signature = 'brevo:test {email : The email address to test with}';

    protected $description = 'Test the Brevo DOI contact API and show the full response';

    public function handle(): int
    {
        $email = $this->argument('email');

        $apiKey = config('brevo.api_key');
        $listId = (int) Setting::get('brevo_list_id');
        $templateId = (int) Setting::get('brevo_doi_template_id');
        $redirectionUrl = route('newsletter.confirmed');

        $this->line('=== Brevo API Test ===');
        $this->line("Email:          {$email}");
        $this->line('API Key:        '.($apiKey ? 'SET ('.strlen($apiKey).' chars)' : 'NOT SET'));
        $this->line("List ID:        {$listId}");
        $this->line("Template ID:    {$templateId}");
        $this->line("Redirection URL:{$redirectionUrl}");
        $this->newLine();

        if (! $apiKey) {
            $this->error('BREVO_API_KEY is not set in .env');

            return self::FAILURE;
        }

        if (! $listId || ! $templateId) {
            $this->error('brevo_list_id or brevo_doi_template_id not configured in settings');

            return self::FAILURE;
        }

        $this->line('Sending DOI contact request...');

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/contacts/doubleOptinConfirmation', [
                'email' => $email,
                'includeListIds' => [$listId],
                'templateId' => $templateId,
                'redirectionUrl' => $redirectionUrl,
            ]);

            if ($response->successful()) {
                $this->info('SUCCESS: DOI confirmation email sent. Check inbox.');
            } else {
                $this->error("API Error (HTTP {$response->status()})");
                $this->line('Response: '.json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        } catch (\Throwable $e) {
            $this->error('Unexpected error: '.get_class($e));
            $this->line($e->getMessage());
        }

        return self::SUCCESS;
    }
}
