<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\BrevoService;
use App\Support\NewsletterSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

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

    public function mount(): void
    {
        $this->form->fill([
            'blog_name' => Setting::get('blog_name', config('app.name')),
            'hero_title' => Setting::get('hero_title', ''),
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
            'newsletter_placement' => Setting::get('newsletter_placement', 'article'),
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
                    Tabs::make('Settings')
                        ->tabs([
                            Tab::make(__('General'))
                                ->schema($this->generalFields()),
                            Tab::make(__('Appearance'))
                                ->columns(2)
                                ->schema($this->appearanceFields()),
                            Tab::make(__('Newsletter'))
                                ->schema($this->newsletterFields()),
                            Tab::make(__('Footer & Scripts'))
                                ->schema($this->footerAndScriptsFields()),
                        ])
                        ->columnSpanFull(),
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

    /**
     * @return array<int, mixed>
     */
    private function generalFields(): array
    {
        return [
            TextInput::make('blog_name')
                ->label(__('Blog Name'))
                ->helperText(__('Used in the navbar and browser tab title.'))
                ->required()
                ->maxLength(255),
            TextInput::make('hero_title')
                ->label(__('Blog Title'))
                ->helperText(__('Large title shown on the homepage. Defaults to Blog Name if left empty.'))
                ->maxLength(255),
            Textarea::make('blog_description')
                ->label(__('Blog Description'))
                ->helperText(__('Used in meta tags and RSS feed.'))
                ->rows(2)
                ->maxLength(300),
            Toggle::make('comments_enabled')
                ->label(__('Enable Comments'))
                ->helperText(__('When disabled, the comment section is hidden on all blog posts. Individual posts can also disable comments.')),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function appearanceFields(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function newsletterFields(): array
    {
        return [
            Toggle::make('newsletter_enabled')
                ->label(__('Enable Newsletter'))
                ->helperText(__('When enabled, a subscription form appears in one chosen location.'))
                ->live(),
            Select::make('newsletter_placement')
                ->label(__('Placement'))
                ->options([
                    'article' => __('At end of blog posts'),
                    'footer' => __('In site footer'),
                ])
                ->default('article')
                ->native(false)
                ->visible(fn (Get $get): bool => (bool) $get('newsletter_enabled'))
                ->required(fn (Get $get): bool => (bool) $get('newsletter_enabled')),
            Placeholder::make('brevo_status')
                ->label(__('Brevo status'))
                ->content(function (): string {
                    $status = NewsletterSettings::brevoStatus();

                    return collect([
                        __('API key: :state', ['state' => $status['api_key'] ? __('configured') : __('missing')]),
                        __('List ID: :state', ['state' => $status['list_id'] ? __('configured') : __('missing')]),
                        __('Template ID: :state', ['state' => $status['template_id'] ? __('configured') : __('missing')]),
                    ])->implode(' · ');
                })
                ->visible(fn (Get $get): bool => (bool) $get('newsletter_enabled')),
            TextInput::make('brevo_list_id')
                ->label(__('Brevo List ID'))
                ->helperText(__('The ID of the Brevo contact list subscribers are added to.'))
                ->numeric()
                ->placeholder('3')
                ->visible(fn (Get $get): bool => (bool) $get('newsletter_enabled')),
            TextInput::make('brevo_doi_template_id')
                ->label(__('Brevo DOI Template ID'))
                ->helperText(__('The ID of the double opt-in confirmation email template in Brevo.'))
                ->numeric()
                ->placeholder('2')
                ->visible(fn (Get $get): bool => (bool) $get('newsletter_enabled')),
            Action::make('sendTestNewsletter')
                ->label(__('Send test email'))
                ->icon(Heroicon::PaperAirplane)
                ->iconSize(IconSize::Small)
                ->color('gray')
                ->visible(fn (Get $get): bool => (bool) $get('newsletter_enabled'))
                ->requiresConfirmation()
                ->modalHeading(__('Send test newsletter email'))
                ->modalDescription(__('Sends a double opt-in test email to your admin account.'))
                ->action(function (): void {
                    $email = Auth::user()?->email;

                    if (blank($email)) {
                        Notification::make()
                            ->title(__('No admin email found'))
                            ->danger()
                            ->send();

                        return;
                    }

                    if (! NewsletterSettings::brevoConfigured()) {
                        Notification::make()
                            ->title(__('Brevo is not fully configured'))
                            ->body(__('Set the API key, list ID, and template ID first.'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $response = app(BrevoService::class)->sendDoubleOptIn($email);

                    if (app(BrevoService::class)->isSuccessfulResponse($response)) {
                        Notification::make()
                            ->title(__('Test email sent'))
                            ->body(__('Check :email for the confirmation message.', ['email' => $email]))
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title(__('Test email failed'))
                        ->body(__('Brevo returned HTTP :status.', ['status' => $response->status()]))
                        ->danger()
                        ->send();
                }),
            Action::make('openBrevo')
                ->label(__('Open Brevo Dashboard'))
                ->icon(Heroicon::ArrowTopRightOnSquare)
                ->iconSize(IconSize::Small)
                ->color('gray')
                ->url('https://app.brevo.com', shouldOpenInNewTab: true),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function footerAndScriptsFields(): array
    {
        return [
            Textarea::make('footer_text')
                ->label(__('Footer Text'))
                ->helperText(__('Additional text shown in the footer.'))
                ->rows(2)
                ->maxLength(500),
            Textarea::make('head_scripts')
                ->label(__('Custom Head Scripts'))
                ->helperText(__('Injected into <head> on public pages. Use for analytics or custom meta tags.'))
                ->rows(3),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $data['comments_enabled'] = $data['comments_enabled'] ? '1' : '0';
        $data['newsletter_enabled'] = $data['newsletter_enabled'] ? '1' : '0';

        Setting::setMany($data);

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }
}
