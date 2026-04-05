<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function publicRouteProvider(): array
    {
        return [
            'home' => ['/', 200],
            'archive' => ['/archiv', 200],
            'feed' => ['/feed', 200],
            'health' => ['/up', 200],
        ];
    }

    #[DataProvider('publicRouteProvider')]
    public function test_public_routes_return_expected_status(string $path, int $expectedStatus): void
    {
        $this->get($path)->assertStatus($expectedStatus);
    }
}
