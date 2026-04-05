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
                Section::make(__('Personal Data'))
                    ->columns(2)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label(__('Profile Picture'))
                            ->helperText(__('If you do not upload a picture, your Gravatar for this email address is shown (same service as blog comment avatars).'))
                            ->collection('avatar')
                            ->image()
                            ->avatar()
                            ->maxSize(1024)
                            ->columnSpanFull(),
                        TextInput::make('username')
                            ->label(__('Username'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->autocomplete('username'),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        Textarea::make('bio')
                            ->label(__('Bio'))
                            ->helperText(__('Short description about you. Shown on the blog.'))
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Social Links'))
                    ->description(__('Full URLs including https://'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('website')
                            ->label(__('Website'))
                            ->url()
                            ->placeholder('https://example.com')
                            ->maxLength(255),
                        TextInput::make('social_github')
                            ->label(__('GitHub'))
                            ->url()
                            ->placeholder('https://github.com/username')
                            ->maxLength(255),
                        TextInput::make('social_twitter')
                            ->label(__('X / Twitter'))
                            ->url()
                            ->placeholder('https://x.com/username')
                            ->maxLength(255),
                        TextInput::make('social_linkedin')
                            ->label(__('LinkedIn'))
                            ->url()
                            ->placeholder('https://linkedin.com/in/username')
                            ->maxLength(255),
                        TextInput::make('social_instagram')
                            ->label(__('Instagram'))
                            ->url()
                            ->placeholder('https://instagram.com/username')
                            ->maxLength(255),
                        TextInput::make('social_bluesky')
                            ->label(__('Bluesky'))
                            ->url()
                            ->placeholder('https://bsky.app/profile/username')
                            ->maxLength(255),
                    ]),
                Section::make(__('Change Password'))
                    ->description(__('Leave blank to keep the current password.'))
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
                ->helperText(__('Min. 8 characters, upper & lowercase, number and special character.'))
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
                ->title(__('Credentials changed successfully.'))
                ->success()
                ->send();
        }
    }
}
