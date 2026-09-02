<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SlugLocaleTest extends TestCase
{
    #[Test]
    public function german_slug_locale_transliterates_umlauts(): void
    {
        $this->assertSame('ueber-laravel', Str::slug('Über Laravel', '-', 'de'));
    }
}
