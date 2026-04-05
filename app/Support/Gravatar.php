<?php

namespace App\Support;

/**
 * Gravatar image URL (same parameters as public blog comments).
 */
final class Gravatar
{
    public static function url(?string $email, int $size = 80, string $default = 'mp'): string
    {
        $hash = md5(strtolower(trim($email ?? '')));

        return 'https://www.gravatar.com/avatar/'.$hash.'?s='.$size.'&d='.urlencode($default);
    }
}
