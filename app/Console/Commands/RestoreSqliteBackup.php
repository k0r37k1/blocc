<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

#[Signature('backup:restore-sqlite
                            {path : Backup zip path relative to the backup disk root (e.g. "blocc/2026-05-07-02-00-00.zip") }
                            {--disk=local : Filesystem disk where backups are stored (default: local) }
                            {--force : Required. Acknowledge that this will overwrite the current database file }
                            {--no-down-check : Allow restore without maintenance mode (not recommended) }')]
#[Description('Restore the SQLite database from a Spatie backup zip (non-production only).')]
class RestoreSqliteBackup extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Refusing to restore backups in production.');

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $this->error('Refusing to restore without --force.');

            return self::FAILURE;
        }

        if (! $this->option('no-down-check') && ! app()->isDownForMaintenance()) {
            $this->error('Refusing to restore while the application is up. Run `php artisan down` first, or pass --no-down-check.');

            return self::FAILURE;
        }

        if (config('database.default') !== 'sqlite') {
            $this->error('This command only supports database.default=sqlite.');

            return self::FAILURE;
        }

        $diskName = (string) $this->option('disk');
        $path = ltrim((string) $this->argument('path'), '/');

        $disk = Storage::disk($diskName);

        if (! $disk->exists($path)) {
            $this->error("Backup zip not found on disk [{$diskName}]: {$path}");

            return self::FAILURE;
        }

        $dbPath = (string) config('database.connections.sqlite.database');

        if ($dbPath === '' || $dbPath === ':memory:') {
            $this->error('SQLite database path is not a file path.');

            return self::FAILURE;
        }

        if (! is_string($dbPath) || $dbPath === '') {
            $this->error('SQLite database path is invalid.');

            return self::FAILURE;
        }

        $zipTmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-restore-'.Str::uuid().'.zip';

        try {
            file_put_contents($zipTmpPath, $disk->get($path));

            $extractDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-restore-'.Str::uuid();
            if (! is_dir($extractDir) && ! mkdir($extractDir, 0o700, true) && ! is_dir($extractDir)) {
                $this->error('Failed to create temporary extraction directory.');

                return self::FAILURE;
            }

            $zip = new ZipArchive;
            if ($zip->open($zipTmpPath) !== true) {
                $this->error('Failed to open backup zip.');

                return self::FAILURE;
            }

            $dumpEntryName = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);
                if (Str::startsWith($name, 'db-dumps/') && Str::endsWith($name, '.sql')) {
                    $dumpEntryName = $name;
                    break;
                }
            }

            if ($dumpEntryName === null) {
                $this->error('No db-dumps/*.sql found in the backup zip. This command expects a database-only backup.');

                return self::FAILURE;
            }

            if (! $zip->extractTo($extractDir, [$dumpEntryName])) {
                $this->error('Failed to extract database dump from zip.');

                return self::FAILURE;
            }

            $zip->close();

            $dumpPath = $extractDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $dumpEntryName);
            if (! file_exists($dumpPath)) {
                $this->error('Extracted dump file not found.');

                return self::FAILURE;
            }

            $sqlite3Path = trim((string) shell_exec('command -v sqlite3 2>/dev/null'));
            if ($sqlite3Path === '') {
                $this->error('sqlite3 CLI not found on this system. Restore cannot proceed.');

                return self::FAILURE;
            }

            $newDbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'restore-'.Str::uuid().'.sqlite';

            $cmd = sprintf(
                '%s %s < %s 2>&1',
                escapeshellcmd($sqlite3Path),
                escapeshellarg($newDbPath),
                escapeshellarg($dumpPath),
            );

            $output = [];
            $exitCode = 0;
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                $this->error('sqlite3 restore failed.');
                $this->line(implode(PHP_EOL, $output));

                return self::FAILURE;
            }

            if (! file_exists($newDbPath) || filesize($newDbPath) === 0) {
                $this->error('Restored database file was not created or is empty.');

                return self::FAILURE;
            }

            $backupOldDbPath = $dbPath.'.before-restore.'.now()->format('YmdHis');
            if (file_exists($dbPath) && ! rename($dbPath, $backupOldDbPath)) {
                $this->error("Failed to move existing DB to: {$backupOldDbPath}");

                return self::FAILURE;
            }

            $targetDir = dirname($dbPath);
            if (! is_dir($targetDir) && ! mkdir($targetDir, 0o755, true) && ! is_dir($targetDir)) {
                $this->error("Failed to create database directory: {$targetDir}");

                return self::FAILURE;
            }

            if (! rename($newDbPath, $dbPath)) {
                $this->error('Failed to replace database file.');

                return self::FAILURE;
            }

            $this->info('SQLite database restored successfully.');
            $this->line("Previous DB saved as: {$backupOldDbPath}");

            return self::SUCCESS;
        } finally {
            if (file_exists($zipTmpPath)) {
                @unlink($zipTmpPath);
            }
        }
    }
}
