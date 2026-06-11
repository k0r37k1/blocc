<?php

namespace App\Console\Commands;

use App\Services\SiteMedia;
use Illuminate\Console\Command;

class ImportSiteMediaCommand extends Command
{
    protected $signature = 'site-media:import
                            {--dry-run : Show how many images would be imported without writing}';

    protected $description = 'Import unique post featured images into site uploads';

    public function handle(SiteMedia $siteMedia): int
    {
        if ($this->option('dry-run')) {
            $stats = $siteMedia->importFromPosts(dryRun: true);

            $this->info("Would process {$stats['processed']} post image(s).");
            $this->info("Would import {$stats['imported']} new upload(s).");
            $this->info("Would skip {$stats['skipped']} duplicate(s) already in uploads.");

            return self::SUCCESS;
        }

        $stats = $siteMedia->importFromPosts();

        $this->info("Processed {$stats['processed']} post image(s).");
        $this->info("Imported {$stats['imported']} new upload(s).");
        $this->info("Skipped {$stats['skipped']} duplicate(s) already in uploads.");

        return self::SUCCESS;
    }
}
