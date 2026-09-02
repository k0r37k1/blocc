<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BrevoService
{
    public function sendDoubleOptIn(string $email): Response
    {
        return Http::withHeaders([
            'api-key' => config('brevo.api_key'),
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/contacts/doubleOptinConfirmation', [
            'email' => $email,
            'includeListIds' => [(int) Setting::get('brevo_list_id')],
            'templateId' => (int) Setting::get('brevo_doi_template_id'),
            'redirectionUrl' => route('newsletter.confirmed'),
        ]);
    }

    public function isSuccessfulResponse(Response $response): bool
    {
        return $response->successful() || $response->status() === 400;
    }
}
