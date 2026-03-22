<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateDoiContact;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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

        try {
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', $apiKey);

            $contactsApi = new ContactsApi(new Client, $config);

            $doiContact = new CreateDoiContact([
                'email' => $email,
                'includeListIds' => [$listId],
                'templateId' => $templateId,
                'redirectionUrl' => $redirectionUrl,
            ]);

            $this->line('Sending DOI contact request...');
            $contactsApi->createDoiContact($doiContact);

            $this->info('SUCCESS: DOI confirmation email sent. Check inbox.');
        } catch (\Brevo\Client\ApiException $e) {
            $this->error("API Error (HTTP {$e->getCode()})");
            $this->line('Message:  '.$e->getMessage());
            $this->line('Response: '.json_encode(json_decode($e->getResponseBody()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            $this->error('Unexpected error: '.get_class($e));
            $this->line($e->getMessage());
        }

        return self::SUCCESS;
    }
}
