<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use ZipArchive;

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

    public function test_command_restores_sqlite_database_from_backup_zip(): void
    {
        $sqlite3 = (new ExecutableFinder)->find('sqlite3');

        if ($sqlite3 === null) {
            $this->markTestSkipped('sqlite3 CLI not available.');
        }

        $tempDir = storage_path('framework/testing/restore-'.Str::uuid());
        File::ensureDirectoryExists($tempDir);

        $dbPath = $tempDir.DIRECTORY_SEPARATOR.'database.sqlite';
        $dumpPath = $tempDir.DIRECTORY_SEPARATOR.'dump.sql';
        $zipTmp = $tempDir.DIRECTORY_SEPARATOR.'backup.zip';
        $zipPath = 'testing/restore-'.Str::uuid().'.zip';

        try {
            Config::set('database.default', 'sqlite');
            Config::set('database.connections.sqlite.database', $dbPath);

            DB::purge('sqlite');
            DB::reconnect('sqlite');

            Artisan::call('migrate', ['--force' => true]);

            $user = User::factory()->create(['email' => 'restore-marker@example.com']);

            $dumpProcess = new Process([$sqlite3, $dbPath, '.dump']);
            $dumpProcess->mustRun();
            File::put($dumpPath, $dumpProcess->getOutput());

            $this->assertFileExists($dumpPath);

            $zip = new ZipArchive;
            $this->assertTrue($zip->open($zipTmp, ZipArchive::CREATE) === true);
            $zip->addFile($dumpPath, 'db-dumps/sqlite-sqlite.sql');
            $zip->close();

            Storage::disk('local')->put($zipPath, File::get($zipTmp));

            User::query()->delete();
            $this->assertDatabaseMissing('users', ['email' => 'restore-marker@example.com']);

            $this->artisan('backup:restore-sqlite', [
                'path' => $zipPath,
                '--force' => true,
                '--no-down-check' => true,
            ])->assertSuccessful();

            DB::purge('sqlite');
            DB::reconnect('sqlite');

            $this->assertDatabaseHas('users', [
                'email' => 'restore-marker@example.com',
                'id' => $user->id,
            ]);
        } finally {
            Storage::disk('local')->delete($zipPath);
            File::deleteDirectory($tempDir);
        }
    }
}
