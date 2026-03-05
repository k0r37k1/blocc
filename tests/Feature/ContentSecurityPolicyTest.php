<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_have_strict_csp(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
    }

    public function test_admin_pages_have_permissive_csp(): void
    {
        $response = $this->get('/admin/login');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("'unsafe-eval'", $csp);
        $this->assertStringContainsString("'unsafe-inline'", $csp);
        $this->assertStringNotContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_public_pages_have_security_headers(): void
    {
        $response = $this->get('/');

        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertNotNull($response->headers->get('Permissions-Policy'));
        $this->assertNull($response->headers->get('X-XSS-Protection'));
    }

    public function test_non_html_responses_do_not_have_csp_headers(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertNull($response->headers->get('Content-Security-Policy'));
        $this->assertNull($response->headers->get('X-Content-Type-Options'));
    }

    public function test_admin_pages_have_security_headers(): void
    {
        $response = $this->get('/admin/login');

        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertNotNull($response->headers->get('Permissions-Policy'));
    }
}
