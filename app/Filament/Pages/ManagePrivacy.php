<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class ManagePrivacy extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $slug = 'privacy';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.manage-privacy';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Cookie Consent');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Datenschutz');
    }

    public function getTitle(): string
    {
        return __('Privacy & Cookie Settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'cookie_consent_enabled' => Setting::get('cookie_consent_enabled', '1') === '1',
            'cookie_policy_url' => Setting::get('cookie_policy_url', ''),

            'functional_cookies_enabled' => Setting::get('functional_cookies_enabled', '0') === '1',

            'google_analytics_enabled' => Setting::get('google_analytics_enabled', '0') === '1',
            'google_analytics_id' => Setting::get('google_analytics_id', ''),
            'google_analytics_anonymize_ip' => Setting::get('google_analytics_anonymize_ip', '1') === '1',

            'matomo_enabled' => Setting::get('matomo_enabled', '0') === '1',
            'matomo_url' => Setting::get('matomo_url', ''),
            'matomo_site_id' => Setting::get('matomo_site_id', ''),

            'marketing_cookies_enabled' => Setting::get('marketing_cookies_enabled', '0') === '1',
            'meta_pixel_id' => Setting::get('meta_pixel_id', ''),

            'google_ads_enabled' => Setting::get('google_ads_enabled', '0') === '1',
            'google_ads_id' => Setting::get('google_ads_id', ''),

            'linkedin_enabled' => Setting::get('linkedin_enabled', '0') === '1',
            'linkedin_partner_id' => Setting::get('linkedin_partner_id', ''),

            'tiktok_enabled' => Setting::get('tiktok_enabled', '0') === '1',
            'tiktok_pixel_id' => Setting::get('tiktok_pixel_id', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Form::make([
                    Section::make(__('Cookie Banner'))
                        ->description(__('Controls whether the cookie consent banner is shown to visitors.'))
                        ->schema([
                            Toggle::make('cookie_consent_enabled')
                                ->label(__('Enable Cookie Banner'))
                                ->helperText(__('When disabled, no cookie consent banner is shown and no tracking scripts are loaded.')),
                            TextInput::make('cookie_policy_url')
                                ->label(__('Privacy Policy URL'))
                                ->helperText(__('Link shown in the cookie banner to your privacy/cookie policy page. Leave empty to hide the link.'))
                                ->url()
                                ->placeholder('https://example.com/datenschutz')
                                ->maxLength(500),
                        ]),

                    Section::make(__('Functional Cookies'))
                        ->description(__('Cookies that enable enhanced functionality and personalisation.'))
                        ->schema([
                            Toggle::make('functional_cookies_enabled')
                                ->label(__('Enable Functional Cookies'))
                                ->helperText(__('Shows a functional cookies category in the consent banner for preferences and extended features.')),
                        ]),

                    Section::make(__('Analytics'))
                        ->description(__('Track visitor behaviour to understand and improve your blog.'))
                        ->schema([
                            Toggle::make('google_analytics_enabled')
                                ->label(__('Enable Google Analytics'))
                                ->helperText(__('Loads GA4 only after the visitor has consented to analytics cookies.')),
                            Toggle::make('google_analytics_anonymize_ip')
                                ->label(__('Anonymize IP'))
                                ->helperText(__('Masks the last octet of visitor IP addresses before sending to Google.')),
                            TextInput::make('google_analytics_id')
                                ->label(__('Google Analytics Measurement ID'))
                                ->helperText(__('Your GA4 Measurement ID, e.g. G-XXXXXXXXXX.'))
                                ->placeholder('G-XXXXXXXXXX')
                                ->maxLength(50),

                            Toggle::make('matomo_enabled')
                                ->label(__('Enable Matomo Analytics'))
                                ->helperText(__('Privacy-friendly self-hosted analytics. Loads only after analytics consent.')),
                            TextInput::make('matomo_url')
                                ->label(__('Matomo URL'))
                                ->helperText(__('The URL of your Matomo instance, e.g. https://analytics.example.com'))
                                ->url()
                                ->placeholder('https://analytics.example.com')
                                ->maxLength(255),
                            TextInput::make('matomo_site_id')
                                ->label(__('Matomo Site ID'))
                                ->helperText(__('The numeric ID of your site in Matomo.'))
                                ->numeric()
                                ->placeholder('1'),
                        ]),

                    Section::make(__('Marketing & Advertising'))
                        ->description(__('Cookies used for personalised advertising and conversion tracking.'))
                        ->schema([
                            Toggle::make('marketing_cookies_enabled')
                                ->label(__('Enable Marketing Cookies'))
                                ->helperText(__('Shows a marketing cookies category in the consent banner.')),

                            TextInput::make('meta_pixel_id')
                                ->label(__('Meta Pixel ID'))
                                ->helperText(__('Your Facebook/Meta Pixel ID. Loads only after marketing consent.'))
                                ->placeholder('123456789012345')
                                ->maxLength(30),

                            Toggle::make('google_ads_enabled')
                                ->label(__('Enable Google Ads'))
                                ->helperText(__('Loads Google Ads conversion tracking only after marketing consent.')),
                            TextInput::make('google_ads_id')
                                ->label(__('Google Ads Conversion ID'))
                                ->helperText(__('Your Google Ads ID, e.g. AW-XXXXXXXXXX.'))
                                ->placeholder('AW-XXXXXXXXXX')
                                ->maxLength(30),

                            Toggle::make('linkedin_enabled')
                                ->label(__('Enable LinkedIn Insight Tag'))
                                ->helperText(__('Loads the LinkedIn Insight Tag for conversion tracking and retargeting.')),
                            TextInput::make('linkedin_partner_id')
                                ->label(__('LinkedIn Partner ID'))
                                ->helperText(__('Found in Campaign Manager → Account Assets → Insight Tag.'))
                                ->placeholder('1234567')
                                ->maxLength(20),

                            Toggle::make('tiktok_enabled')
                                ->label(__('Enable TikTok Pixel'))
                                ->helperText(__('Loads the TikTok Pixel for ad conversion tracking.')),
                            TextInput::make('tiktok_pixel_id')
                                ->label(__('TikTok Pixel ID'))
                                ->helperText(__('Found in TikTok Ads Manager → Assets → Events.'))
                                ->placeholder('ABCDEFGHIJKLMNO')
                                ->maxLength(50),
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

        Setting::setMany([
            'cookie_consent_enabled' => $data['cookie_consent_enabled'] ? '1' : '0',
            'cookie_policy_url' => $data['cookie_policy_url'] ?? '',

            'functional_cookies_enabled' => $data['functional_cookies_enabled'] ? '1' : '0',

            'google_analytics_enabled' => $data['google_analytics_enabled'] ? '1' : '0',
            'google_analytics_id' => $data['google_analytics_id'] ?? '',
            'google_analytics_anonymize_ip' => $data['google_analytics_anonymize_ip'] ? '1' : '0',

            'matomo_enabled' => $data['matomo_enabled'] ? '1' : '0',
            'matomo_url' => $data['matomo_url'] ?? '',
            'matomo_site_id' => $data['matomo_site_id'] ?? '',

            'marketing_cookies_enabled' => $data['marketing_cookies_enabled'] ? '1' : '0',
            'meta_pixel_id' => $data['meta_pixel_id'] ?? '',

            'google_ads_enabled' => $data['google_ads_enabled'] ? '1' : '0',
            'google_ads_id' => $data['google_ads_id'] ?? '',

            'linkedin_enabled' => $data['linkedin_enabled'] ? '1' : '0',
            'linkedin_partner_id' => $data['linkedin_partner_id'] ?? '',

            'tiktok_enabled' => $data['tiktok_enabled'] ? '1' : '0',
            'tiktok_pixel_id' => $data['tiktok_pixel_id'] ?? '',
        ]);

        Notification::make()
            ->title(__('Privacy settings saved'))
            ->success()
            ->send();
    }
}
