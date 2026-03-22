<?php

namespace App\Livewire;

use App\Models\Setting;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateDoiContact;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

class NewsletterSubscribe extends Component
{
    public string $email = '';

    /** Honeypot field — must remain empty. */
    public string $website = '';

    /** Timestamp when form was rendered (spam time check). */
    public int $formLoadedAt = 0;

    public string $successMessage = '';

    public string $errorMessage = '';

    /** Display variant: 'footer' (underline input) or 'card' (bordered input + filled button). */
    public string $variant = 'footer';

    public function mount(string $variant = 'footer'): void
    {
        $this->formLoadedAt = now()->timestamp;
        $this->variant = $variant;
    }

    public function render(): View
    {
        return view('livewire.newsletter-subscribe');
    }

    public function subscribe(): void
    {
        if ($this->isSpam()) {
            $this->successMessage = __('Thank you! Please check your inbox to confirm your subscription.');

            return;
        }

        $this->validate([
            'email' => 'required|email|max:255',
        ]);

        $rateLimitKey = 'newsletter:'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $this->errorMessage = __('Too many attempts. Please try again later.');

            return;
        }

        RateLimiter::hit($rateLimitKey, 3600);

        $listId = (int) Setting::get('brevo_list_id');
        $templateId = (int) Setting::get('brevo_doi_template_id');
        $redirectionUrl = route('newsletter.confirmed');

        if (! $listId || ! $templateId) {
            Log::error('Newsletter: brevo_list_id or brevo_doi_template_id not configured.');
            $this->errorMessage = __('Newsletter is temporarily unavailable. Please try again later.');

            return;
        }

        try {
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', config('brevo.api_key'));

            $contactsApi = new \Brevo\Client\Api\ContactsApi(new Client, $config);

            $doiContact = new CreateDoiContact([
                'email' => $this->email,
                'includeListIds' => [$listId],
                'templateId' => $templateId,
                'redirectionUrl' => $redirectionUrl,
            ]);

            $contactsApi->createDoiContact($doiContact);

            $this->successMessage = __('Thank you! Please check your inbox to confirm your subscription.');
            $this->reset('email', 'website');
        } catch (\Brevo\Client\ApiException $e) {
            Log::error('Newsletter subscription failed', [
                'email' => $this->email,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);

            // 400 with "Contact already exist" or similar — treat as success to avoid enumeration
            if ($e->getCode() === 400) {
                $this->successMessage = __('Thank you! Please check your inbox to confirm your subscription.');
                $this->reset('email', 'website');

                return;
            }

            $this->errorMessage = __('Something went wrong. Please try again later.');
        } catch (\Throwable $e) {
            Log::error('Newsletter unexpected error', [
                'email' => $this->email,
                'class' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            $this->errorMessage = __('Something went wrong. Please try again later.');
        }
    }

    private function isSpam(): bool
    {
        if (filled($this->website)) {
            return true;
        }

        if ($this->formLoadedAt > 0 && (now()->timestamp - $this->formLoadedAt) < 3) {
            return true;
        }

        return false;
    }
}
