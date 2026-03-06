<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCredentialsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Filament::auth()->user();

        if ($user !== null && $user->must_change_credentials && ! $request->routeIs('filament.admin.auth.profile')) {
            Notification::make()
                ->title('Bitte ändere dein Passwort.')
                ->warning()
                ->persistent()
                ->send();

            /** @var string $profileUrl */
            $profileUrl = Filament::getProfileUrl();

            return redirect()->to($profileUrl);
        }

        return $next($request);
    }
}
