<?php

namespace Tests\Feature;

use App\Livewire\NewsletterSubscribe;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NewsletterSubscribeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('brevo_list_id', '3');
        Setting::set('brevo_doi_template_id', '2');
        Setting::set('newsletter_enabled', '1');

        RateLimiter::clear('newsletter:127.0.0.1');
    }

    #[Test]
    public function it_renders_the_subscribe_form(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->assertSee(__('Subscribe'))
            ->assertSee(__('Newsletter'));
    }

    #[Test]
    public function it_requires_a_valid_email(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->set('formLoadedAt', now()->subSeconds(10)->timestamp)
            ->set('email', 'not-an-email')
            ->set('consent', true)
            ->call('subscribe')
            ->assertHasErrors(['email' => 'email']);
    }

    #[Test]
    public function it_requires_consent(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->set('formLoadedAt', now()->subSeconds(10)->timestamp)
            ->set('email', 'user@example.com')
            ->set('consent', false)
            ->call('subscribe')
            ->assertHasErrors(['consent']);
    }

    #[Test]
    public function it_silently_passes_when_honeypot_is_filled(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->set('email', 'bot@example.com')
            ->set('consent', true)
            ->set('website', 'http://spam.com')
            ->call('subscribe')
            ->assertSet('successMessage', __('Thank you! Please check your inbox to confirm your subscription.'))
            ->assertHasNoErrors();
    }

    #[Test]
    public function it_shows_error_when_brevo_not_configured(): void
    {
        Setting::set('brevo_list_id', '');
        Setting::set('brevo_doi_template_id', '');

        Livewire::test(NewsletterSubscribe::class)
            ->set('formLoadedAt', now()->subSeconds(10)->timestamp)
            ->set('email', 'user@example.com')
            ->set('consent', true)
            ->call('subscribe')
            ->assertSet('errorMessage', __('Newsletter is temporarily unavailable. Please try again later.'));
    }

    #[Test]
    public function it_enforces_rate_limiting(): void
    {
        RateLimiter::hit('newsletter:127.0.0.1', 3600);
        RateLimiter::hit('newsletter:127.0.0.1', 3600);
        RateLimiter::hit('newsletter:127.0.0.1', 3600);

        Livewire::test(NewsletterSubscribe::class)
            ->set('formLoadedAt', now()->subSeconds(10)->timestamp)
            ->set('email', 'user@example.com')
            ->set('consent', true)
            ->call('subscribe')
            ->assertSet('errorMessage', __('Too many attempts. Please try again later.'));
    }

    #[Test]
    public function newsletter_confirmed_page_is_accessible(): void
    {
        $this->get(route('newsletter.confirmed'))
            ->assertOk()
            ->assertSee(__('Subscription confirmed!'));
    }
}
