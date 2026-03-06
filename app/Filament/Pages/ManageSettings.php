<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

/**
 * @property-read Schema $form
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 99;

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('General');
    }

    public function getTitle(): string
    {
        return __('Blog Settings');
    }

    protected string $view = 'filament.pages.manage-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backup')
                ->label(__('Backup'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading(__('Create Backup'))
                ->modalDescription(__('This will create a full backup of the database and files. This may take a moment.'))
                ->action(function (): void {
                    Artisan::call('backup:run', ['--only-db' => true]);

                    Notification::make()
                        ->title(__('Backup created'))
                        ->body(__('Database backup was created successfully.'))
                        ->success()
                        ->send();
                }),

            Action::make('resetData')
                ->label(__('Reset Data'))
                ->icon(Heroicon::ArrowPath)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('Reset all data'))
                ->modalDescription(__('This will delete ALL posts, pages, categories, tags, and media. Settings and your user account will be kept. This cannot be undone!'))
                ->modalSubmitActionLabel(__('Yes, delete everything'))
                ->action(function (): void {
                    Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

                    Notification::make()
                        ->title(__('Data reset'))
                        ->body(__('All data has been deleted and default seed data was restored.'))
                        ->warning()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }

    public function mount(): void
    {
        $this->form->fill([
            'blog_name' => Setting::get('blog_name', config('app.name')),
            'blog_description' => Setting::get('blog_description', config('app.description', '')),
            'blog_logo' => filled(Setting::get('blog_logo')) ? [Setting::get('blog_logo')] : [],
            'posts_per_page' => Setting::get('posts_per_page', '10'),
            'social_website' => Setting::get('social_website', ''),
            'social_github' => Setting::get('social_github', ''),
            'social_twitter' => Setting::get('social_twitter', ''),
            'social_linkedin' => Setting::get('social_linkedin', ''),
            'social_instagram' => Setting::get('social_instagram', ''),
            'social_bluesky' => Setting::get('social_bluesky', ''),
            'footer_text' => Setting::get('footer_text', ''),
            'head_scripts' => Setting::get('head_scripts', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Form::make([
                    Section::make(__('General'))
                        ->description(__('Basic blog configuration'))
                        ->columns(2)
                        ->schema([
                            TextInput::make('blog_name')
                                ->label(__('Blog Name'))
                                ->required()
                                ->maxLength(255),
                            Textarea::make('blog_description')
                                ->label(__('Blog Description'))
                                ->helperText(__('Used in meta tags and RSS feed.'))
                                ->rows(2)
                                ->maxLength(300)
                                ->columnSpanFull(),
                            FileUpload::make('blog_logo')
                                ->label(__('Blog Logo'))
                                ->helperText(__('Shown in the header next to the blog name. Recommended: PNG or SVG with transparent background.'))
                                ->image()
                                ->disk('public')
                                ->directory('logo')
                                ->maxSize(512)
                                ->imagePreviewHeight('80')
                                ->columnSpanFull(),
                            TextInput::make('posts_per_page')
                                ->label(__('Posts per Page'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(10),
                        ]),
                    Section::make(__('Social Links'))
                        ->description(__('Full URLs including https://'))
                        ->columns(2)
                        ->schema([
                            TextInput::make('social_website')
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
                    Section::make(__('Footer & Scripts'))
                        ->description(__('Custom footer text and head scripts'))
                        ->schema([
                            Textarea::make('footer_text')
                                ->label(__('Footer Text'))
                                ->helperText(__('Additional text shown in the footer.'))
                                ->rows(2)
                                ->maxLength(500),
                            Textarea::make('head_scripts')
                                ->label(__('Custom Head Scripts'))
                                ->helperText(__('Injected into <head> on public pages. Use for analytics or custom meta tags.'))
                                ->rows(3),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('Save Settings'))
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $logo = $data['blog_logo'] ?? [];
        if (is_array($logo)) {
            $first = reset($logo);
            $data['blog_logo'] = $first !== false ? $first : null;
        } else {
            $data['blog_logo'] = $logo;
        }

        Setting::setMany($data);

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }
}
