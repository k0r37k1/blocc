<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RestoreSqliteBackupCommandTest extends TestCase
{
    public function test_command_refuses_to_run_in_production(): void
    {
        Config::set('app.env', 'production');

        $this->artisan('backup:restore-sqlite some.zip --force')
            ->assertFailed();
    }

    public function test_command_refuses_to_run_without_force(): void
    {
        Config::set('app.env', 'local');

        $this->artisan('backup:restore-sqlite some.zip')
            ->assertFailed();
    }
}
