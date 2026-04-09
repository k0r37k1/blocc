<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_found_page_is_noindex(): void
    {
        $response = $this->get('/definitely-missing-page');

        $response->assertStatus(404);
        $response->assertSee('<meta name="robots" content="noindex, follow">', false);
    }
}
