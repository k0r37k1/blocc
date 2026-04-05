<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;

class AnonymizeCommentIps extends Command
{
    protected $signature = 'comments:anonymize-ips
                            {--dry-run : List how many rows would be cleared without updating}';

    protected $description = 'Clear stored comment IP addresses older than the configured retention period';

    public function handle(): int
    {
        $days = config('comments.ip_retention_days');
        $cutoff = now()->subDays($days);

        $count = Comment::query()
            ->whereNotNull('ip_address')
            ->where('created_at', '<', $cutoff)
            ->count();

        if ($count === 0) {
            $this->info('No comment IPs to anonymize.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Would clear ip_address on {$count} comment(s) (older than {$days} days, before {$cutoff->toDateTimeString()}).");

            return self::SUCCESS;
        }

        $updated = Comment::query()
            ->whereNotNull('ip_address')
            ->where('created_at', '<', $cutoff)
            ->update(['ip_address' => null]);

        $this->info("Cleared ip_address on {$updated} comment(s) (retention: {$days} days).");

        return self::SUCCESS;
    }
}
