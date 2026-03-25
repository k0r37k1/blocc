<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncryptCookies;

class EncryptCookies extends BaseEncryptCookies
{
    public function __construct(Encrypter $encrypter)
    {
        parent::__construct($encrypter);

        // The cookie-consent package stores plain JSON — exclude it from encryption.
        // Using disableFor() (instance-level $except) instead of the static except()
        // so the exclusion is never cleared between requests.
        $this->disableFor(config('cookieconsent.cookie.name'));
    }
}
