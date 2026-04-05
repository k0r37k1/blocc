<?php

namespace App\Filament\AvatarProviders;

use App\Support\Gravatar;
use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class GravatarAvatarProvider implements AvatarProvider
{
    private const int AVATAR_SIZE = 128;

    public function get(Model|Authenticatable $record): string
    {
        $email = $record->getAttribute('email');

        return Gravatar::url(is_string($email) ? $email : '', self::AVATAR_SIZE, 'mp');
    }
}
