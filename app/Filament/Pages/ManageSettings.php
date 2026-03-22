<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\Site;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\SpatieLaravelMediaLibraryPlugin\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Support\Enums\IconSize;
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
            'posts_per_page' => Setting::get('posts_per_page', '10'),
            'accent_color' => Setting::get('accent_color', '#16a34a'),
            'accent_color_dark' => Setting::get('accent_color_dark', '#4ade80'),
            'hero_title_size' => Setting::get('hero_title_size', 'L'),
            'hero_subtitle_size' => Setting::get('hero_subtitle_size', 'M'),
            'post_title_size' => Setting::get('post_title_size', 'M'),
            'heading_font' => Setting::get('heading_font', 'Inter'),
            'body_font' => Setting::get('body_font', 'Inter'),
            'code_theme' => Setting::get('code_theme', 'GitHub'),
            'comments_enabled' => Setting::get('comments_enabled', '1') === '1',
            'footer_text' => Setting::get('footer_text', ''),
            'head_scripts' => Setting::get('head_scripts', ''),
            'newsletter_enabled' => Setting::get('newsletter_enabled', '0') === '1',
            'brevo_list_id' => Setting::get('brevo_list_id', ''),
            'brevo_doi_template_id' => Setting::get('brevo_doi_template_id', ''),
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
                        ->schema([
                            TextInput::make('blog_name')
                                ->label(__('Blog Name'))
                                ->required()
                                ->maxLength(255),
                            Grid::make(3)
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('logo_light')
                                        ->label(__('Logo (Light)'))
                                        ->helperText(__('Shown in the header in light mode. SVG or PNG with transparent background.'))
                                        ->collection('logo_light')
                                        ->model(Site::instance())
                                        ->image()
                                        ->maxSize(1024)
                                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp']),
                                    SpatieMediaLibraryFileUpload::make('logo_dark')
                                        ->label(__('Logo (Dark)'))
                                        ->helperText(__('Shown in the header in dark mode. Falls back to light logo if not set.'))
                                        ->collection('logo_dark')
                                        ->model(Site::instance())
                                        ->image()
                                        ->maxSize(1024)
                                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp']),
                                    SpatieMediaLibraryFileUpload::make('favicon')
                                        ->label(__('Favicon'))
                                        ->helperText(__('Browser tab icon. SVG or 32x32px PNG.'))
                                        ->collection('favicon')
                                        ->model(Site::instance())
                                        ->image()
                                        ->maxSize(256)
                                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/x-icon', 'image/vnd.microsoft.icon']),
                                ]),
                            Textarea::make('blog_description')
                                ->label(__('Blog Description'))
                                ->helperText(__('Used in meta tags and RSS feed.'))
                                ->rows(2)
                                ->maxLength(300),
                        ]),
                    Section::make(__('Appearance'))
                        ->description(__('Colors, fonts, and code style.'))
                        ->columns(2)
                        ->schema([
                            ColorPicker::make('accent_color')
                                ->label(__('Accent Color'))
                                ->helperText(__('Primary color for links, buttons, and highlights (light mode).'))
                                ->required(),
                            ColorPicker::make('accent_color_dark')
                                ->label(__('Accent Color (Dark Mode)'))
                                ->helperText(__('Lighter variant used in dark mode for better readability.')),
                            Select::make('hero_title_size')
                                ->label(__('Hero Title Size'))
                                ->helperText(__('Font size of the blog title on the homepage.'))
                                ->options([
                                    'S' => __('Small'),
                                    'M' => __('Medium'),
                                    'L' => __('Large'),
                                    'XL' => __('Extra Large'),
                                ])
                                ->native(false)
                                ->required(),
                            Select::make('hero_subtitle_size')
                                ->label(__('Hero Subtitle Size'))
                                ->helperText(__('Font size of the blog description on the homepage.'))
                                ->options([
                                    'S' => __('Small'),
                                    'M' => __('Medium'),
                                    'L' => __('Large'),
                                    'XL' => __('Extra Large'),
                                ])
                                ->native(false)
                                ->required(),
                            Select::make('post_title_size')
                                ->label(__('Post Title Size'))
                                ->helperText(__('Font size of post titles in the blog list.'))
                                ->options([
                                    'S' => __('Small'),
                                    'M' => __('Medium'),
                                    'L' => __('Large'),
                                    'XL' => __('Extra Large'),
                                ])
                                ->native(false)
                                ->required(),
                            Select::make('heading_font')
                                ->label(__('Heading Font'))
                                ->helperText(__('Font used for headings (h1-h6).'))
                                ->options(array_combine(
                                    array_keys(config('appearance.fonts')),
                                    array_keys(config('appearance.fonts')),
                                ))
                                ->native(false)
                                ->required(),
                            Select::make('body_font')
                                ->label(__('Body Font'))
                                ->helperText(__('Font used for body text and navigation.'))
                                ->options(array_combine(
                                    array_keys(config('appearance.fonts')),
                                    array_keys(config('appearance.fonts')),
                                ))
                                ->native(false)
                                ->required(),
                            Select::make('code_theme')
                                ->label(__('Code Theme'))
                                ->helperText(__('Syntax highlighting theme for code blocks (light & dark).'))
                                ->options(array_combine(
                                    array_keys(config('appearance.code_themes')),
                                    array_keys(config('appearance.code_themes')),
                                ))
                                ->native(false)
                                ->required(),
                            TextInput::make('posts_per_page')
                                ->label(__('Posts per Page'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(10),
                        ]),
                    Section::make(__('Comments'))
                        ->description(__('Enable or disable comments on blog posts.'))
                        ->schema([
                            Toggle::make('comments_enabled')
                                ->label(__('Enable Comments'))
                                ->helperText(__('When disabled, the comment section is hidden on all blog posts. Individual posts can also disable comments.')),
                        ]),
                    Section::make(__('Newsletter'))
                        ->description(__('Show a newsletter subscription form in the footer.'))
                        ->schema([
                            Toggle::make('newsletter_enabled')
                                ->label(__('Enable Newsletter'))
                                ->helperText(__('When enabled, a subscription form appears in the footer.')),
                            TextInput::make('brevo_list_id')
                                ->label(__('Brevo List ID'))
                                ->helperText(__('The ID of the Brevo contact list subscribers are added to.'))
                                ->numeric()
                                ->placeholder('3'),
                            TextInput::make('brevo_doi_template_id')
                                ->label(__('Brevo DOI Template ID'))
                                ->helperText(__('The ID of the double opt-in confirmation email template in Brevo.'))
                                ->numeric()
                                ->placeholder('2'),
                            Action::make('openBrevo')
                                ->label(__('Open Brevo Dashboard'))
                                ->icon(Heroicon::ArrowTopRightOnSquare)
                                ->iconSize(IconSize::Small)
                                ->color('gray')
                                ->url('https://app.brevo.com', shouldOpenInNewTab: true),
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

        unset($data['logo_light'], $data['logo_dark'], $data['favicon']);

        $data['comments_enabled'] = $data['comments_enabled'] ? '1' : '0';
        $data['newsletter_enabled'] = $data['newsletter_enabled'] ? '1' : '0';

        Setting::setMany($data);

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }
}
