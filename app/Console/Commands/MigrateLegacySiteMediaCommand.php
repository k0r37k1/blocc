<?php

namespace App\Console\Commands;

use App\Services\SiteMedia;
use Illuminate\Console\Command;

class MigrateLegacySiteMediaCommand extends Command
{
    protected $signature = 'site-media:migrate-legacy
                            {--dry-run : Show what would be migrated without writing}';

    protected $description = 'Move legacy site media records into uploads';

    public function handle(SiteMedia $siteMedia): int
    {
        if ($this->option('dry-run')) {
            $stats = $siteMedia->migrateLegacy(dryRun: true);

            $this->info("Would migrate {$stats['migrated']} legacy image(s) into uploads.");
            $this->info("Would skip {$stats['skipped']} duplicate(s) already in uploads.");

            return self::SUCCESS;
        }

        $stats = $siteMedia->migrateLegacy();

        $this->info("Migrated {$stats['migrated']} legacy image(s) into uploads.");
        $this->info("Skipped {$stats['skipped']} duplicate(s) already in uploads.");
        $this->info("Removed {$stats['deleted']} legacy file record(s).");

        return self::SUCCESS;
    }
}
