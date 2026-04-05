<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Comment IP retention (days)
    |--------------------------------------------------------------------------
    |
    | IP addresses stored on comments are cleared after this many days from
    | the comment's created_at timestamp. Document this retention in your
    | privacy policy (DSGVO: purpose, legal basis, storage period).
    |
    */

    'ip_retention_days' => max(1, (int) env('COMMENT_IP_RETENTION_DAYS', 30)),

];
