<?php

namespace App\Support;

use App\Models\Setting;

class NewsletterSettings
{
    public static function enabled(): bool
    {
        return Setting::get('newsletter_enabled', '0') === '1';
    }

    public static function placement(): string
    {
        $placement = Setting::get('newsletter_placement', 'article');

        return in_array($placement, ['article', 'footer'], true) ? $placement : 'article';
    }

    public static function showOnArticles(): bool
    {
        return self::enabled() && self::placement() === 'article';
    }

    public static function showInFooter(): bool
    {
        return self::enabled() && self::placement() === 'footer';
    }

    /**
     * @return array{api_key: bool, list_id: bool, template_id: bool}
     */
    public static function brevoStatus(): array
    {
        return [
            'api_key' => filled(config('brevo.api_key')),
            'list_id' => filled(Setting::get('brevo_list_id')),
            'template_id' => filled(Setting::get('brevo_doi_template_id')),
        ];
    }

    public static function brevoConfigured(): bool
    {
        $status = self::brevoStatus();

        return $status['api_key'] && $status['list_id'] && $status['template_id'];
    }
}
