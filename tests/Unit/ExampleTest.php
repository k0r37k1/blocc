<?php

namespace Tests\Unit;

use App\Support\Gravatar;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_gravatar_url_hashes_email(): void
    {
        $url = Gravatar::url('User@Example.com', 80, 'mp');

        $this->assertStringContainsString(md5('user@example.com'), $url);
        $this->assertStringContainsString('s=80', $url);
        $this->assertStringContainsString('d='.urlencode('mp'), $url);
    }

    public function test_gravatar_url_handles_null_email(): void
    {
        $url = Gravatar::url(null);

        $this->assertStringContainsString(md5(''), $url);
    }
}
