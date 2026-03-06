<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(false)
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Persönliche Daten')
                    ->columns(2)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Profilbild')
                            ->collection('avatar')
                            ->image()
                            ->avatar()
                            ->maxSize(1024)
                            ->columnSpanFull(),
                        TextInput::make('username')
                            ->label('Benutzername')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->autocomplete('username'),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        Textarea::make('bio')
                            ->label('Bio')
                            ->helperText('Kurze Beschreibung über dich. Wird im Blog angezeigt.')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make('Social Links')
                    ->description('Vollständige URLs inkl. https://')
                    ->columns(2)
                    ->schema([
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->placeholder('https://example.com')
                            ->maxLength(255),
                        TextInput::make('social_github')
                            ->label('GitHub')
                            ->url()
                            ->placeholder('https://github.com/username')
                            ->maxLength(255),
                        TextInput::make('social_twitter')
                            ->label('X / Twitter')
                            ->url()
                            ->placeholder('https://x.com/username')
                            ->maxLength(255),
                        TextInput::make('social_linkedin')
                            ->label('LinkedIn')
                            ->url()
                            ->placeholder('https://linkedin.com/in/username')
                            ->maxLength(255),
                        TextInput::make('social_instagram')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/username')
                            ->maxLength(255),
                        TextInput::make('social_bluesky')
                            ->label('Bluesky')
                            ->url()
                            ->placeholder('https://bsky.app/profile/username')
                            ->maxLength(255),
                    ]),
                Section::make('Passwort ändern')
                    ->description('Leer lassen um das aktuelle Passwort beizubehalten.')
                    ->columns(2)
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        $component = parent::getPasswordFormComponent();

        if ($component instanceof TextInput) {
            $component
                ->helperText('Min. 8 Zeichen, Groß- & Kleinbuchstaben, Zahl und Sonderzeichen.')
                ->rule(
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                );
        }

        /** @var \App\Models\User $user */
        $user = $this->getUser();

        if ($user->must_change_credentials && $component instanceof TextInput) {
            $component->required();
        }

        return $component;
    }

    protected function afterSave(): void
    {
        /** @var \App\Models\User $user */
        $user = $this->getUser();

        if ($user->must_change_credentials) {
            $user->update(['must_change_credentials' => false]);

            Notification::make()
                ->title('Zugangsdaten erfolgreich geändert.')
                ->success()
                ->send();
        }
    }
}
