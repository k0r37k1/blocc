<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncryptCookies;

class EncryptCookies extends BaseEncryptCookies
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * The cookie-consent package stores plain JSON in this cookie, so it must
     * be excluded from encryption. We use the instance-level $except property
     * instead of the static $neverEncrypt array, because flushState() resets
     * static::$neverEncrypt between requests and would clear our exclusion.
     *
     * @var array<int, string>
     */
    protected $except = [
        'kopfsalat_cookie_consent',
    ];
}
