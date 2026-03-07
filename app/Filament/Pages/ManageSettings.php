<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
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
            'accent_color' => Setting::get('accent_color', '#16a34a'),
            'accent_color_dark' => Setting::get('accent_color_dark', '#4ade80'),
            'heading_font' => Setting::get('heading_font', 'Inter'),
            'body_font' => Setting::get('body_font', 'Inter'),
            'code_theme' => Setting::get('code_theme', 'GitHub'),
            'favicon' => filled(Setting::get('favicon')) ? [Setting::get('favicon')] : [],
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
                        ->schema([
                            TextInput::make('blog_name')
                                ->label(__('Blog Name'))
                                ->required()
                                ->maxLength(255),
                            Grid::make(2)
                                ->schema([
                                    FileUpload::make('blog_logo')
                                        ->label(__('Blog Logo'))
                                        ->helperText(__('Shown in the header next to the blog name. Recommended: PNG or SVG with transparent background.'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('logo')
                                        ->maxSize(512),
                                    FileUpload::make('favicon')
                                        ->label(__('Favicon'))
                                        ->helperText(__('Browser tab icon. Recommended: 32x32px PNG or ICO.'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('favicon')
                                        ->maxSize(128)
                                        ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/svg+xml', 'image/vnd.microsoft.icon'])
                                        ->imageCropAspectRatio('1:1'),
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

        foreach (['blog_logo', 'favicon'] as $fileField) {
            $file = $data[$fileField] ?? [];
            if (is_array($file)) {
                $first = reset($file);
                $data[$fileField] = $first !== false ? $first : null;
            } else {
                $data[$fileField] = $file;
            }
        }

        Setting::setMany($data);

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }
}
