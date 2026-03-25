<?php

namespace App\Providers;

use App\Models\Setting;
use Whitecube\LaravelCookieConsent\Consent;
use Whitecube\LaravelCookieConsent\CookiesServiceProvider as ServiceProvider;
use Whitecube\LaravelCookieConsent\Facades\Cookies;

class CookiesServiceProvider extends ServiceProvider
{
    /**
     * Define the cookies users should be aware of.
     */
    protected function registerCookies(): void
    {
        if (Setting::get('cookie_consent_enabled', '1') !== '1') {
            return;
        }

        Cookies::essentials()
            ->session()
            ->csrf();

        $this->registerFunctional();
        $this->registerAnalytics();
        $this->registerMarketing();
    }

    protected function registerFunctional(): void
    {
        if (Setting::get('functional_cookies_enabled', '0') !== '1') {
            return;
        }

        Cookies::category('functional');
        Cookies::functional()
            ->name('preferences')
            ->description('Stores your site preferences such as language or display settings.')
            ->duration(60 * 24 * 365);
    }

    protected function registerAnalytics(): void
    {
        $googleAnalyticsId = Setting::get('google_analytics_enabled', '0') === '1'
            ? Setting::get('google_analytics_id')
            : null;

        $matomoEnabled = Setting::get('matomo_enabled', '0') === '1';
        $matomoUrl = Setting::get('matomo_url');
        $matomoSiteId = Setting::get('matomo_site_id');

        if (! $googleAnalyticsId && ! ($matomoEnabled && $matomoUrl && $matomoSiteId)) {
            return;
        }

        $analytics = Cookies::analytics();

        if ($googleAnalyticsId) {
            $analytics->google(
                id: $googleAnalyticsId,
                anonymizeIp: Setting::get('google_analytics_anonymize_ip', '1') === '1',
            );
        }

        if ($matomoEnabled && $matomoUrl && $matomoSiteId) {
            $matomoUrl = rtrim($matomoUrl, '/');
            $analytics
                ->name('_pk_id')
                ->description('Matomo: Identifies a unique visitor. Contains the unique visitor ID and the first visit timestamp.')
                ->duration(60 * 24 * 393)
                ->accepted(function (Consent $consent) use ($matomoUrl, $matomoSiteId): void {
                    $consent->script(<<<HTML
                    <script>
                        var _paq = window._paq = window._paq || [];
                        _paq.push(['trackPageView']);
                        _paq.push(['enableLinkTracking']);
                        (function() {
                            var u="{$matomoUrl}/";
                            _paq.push(['setTrackerUrl', u+'matomo.php']);
                            _paq.push(['setSiteId', '{$matomoSiteId}']);
                            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                            g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
                        })();
                    </script>
                    HTML);
                });
        }
    }

    protected function registerMarketing(): void
    {
        $metaPixelId = Setting::get('marketing_cookies_enabled', '0') === '1'
            ? Setting::get('meta_pixel_id')
            : null;

        $googleAdsId = Setting::get('google_ads_enabled', '0') === '1'
            ? Setting::get('google_ads_id')
            : null;

        $linkedinPartnerId = Setting::get('linkedin_enabled', '0') === '1'
            ? Setting::get('linkedin_partner_id')
            : null;

        $tiktokPixelId = Setting::get('tiktok_enabled', '0') === '1'
            ? Setting::get('tiktok_pixel_id')
            : null;

        if (! $metaPixelId && ! $googleAdsId && ! $linkedinPartnerId && ! $tiktokPixelId) {
            return;
        }

        Cookies::category('marketing');
        $marketing = Cookies::marketing();

        if ($metaPixelId) {
            $marketing
                ->name('_fbp')
                ->description('Meta (Facebook): Used to deliver advertisements and track conversions.')
                ->duration(60 * 24 * 90)
                ->accepted(function (Consent $consent) use ($metaPixelId): void {
                    $consent->script(<<<HTML
                    <script>
                        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){
                        n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                        n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;
                        s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
                        (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
                        fbq('init','{$metaPixelId}');fbq('track','PageView');
                    </script>
                    HTML);
                });
        }

        if ($googleAdsId) {
            $marketing
                ->name('_gcl_au')
                ->description('Google Ads: Used to store and track conversions from Google Ads campaigns.')
                ->duration(60 * 24 * 90)
                ->accepted(function (Consent $consent) use ($googleAdsId): void {
                    $consent->script(<<<HTML
                    <script async src="https://www.googletagmanager.com/gtag/js?id={$googleAdsId}"></script>
                    <script>
                        window.dataLayer=window.dataLayer||[];
                        function gtag(){dataLayer.push(arguments);}
                        gtag('js',new Date());
                        gtag('config','{$googleAdsId}');
                    </script>
                    HTML);
                });
        }

        if ($linkedinPartnerId) {
            $marketing
                ->name('li_fat_id')
                ->description('LinkedIn: Used for conversion tracking and retargeting via the LinkedIn Insight Tag.')
                ->duration(60 * 24 * 30)
                ->accepted(function (Consent $consent) use ($linkedinPartnerId): void {
                    $consent->script(<<<HTML
                    <script>
                        _linkedin_partner_id="{$linkedinPartnerId}";
                        window._linkedin_data_partner_ids=window._linkedin_data_partner_ids||[];
                        window._linkedin_data_partner_ids.push(_linkedin_partner_id);
                        (function(l){if(!l){window.lintrk=function(a,b){window.lintrk.q.push([a,b])};
                        window.lintrk.q=[]}var s=document.getElementsByTagName("script")[0];
                        var b=document.createElement("script");b.type="text/javascript";b.async=true;
                        b.src="https://snap.licdn.com/li.lms-analytics/insight.min.js";
                        s.parentNode.insertBefore(b,s)})(window.lintrk);
                    </script>
                    HTML);
                });
        }

        if ($tiktokPixelId) {
            $marketing
                ->name('_ttp')
                ->description('TikTok: Used to measure advertising effectiveness and track conversions.')
                ->duration(60 * 24 * 395)
                ->accepted(function (Consent $consent) use ($tiktokPixelId): void {
                    $consent->script(<<<HTML
                    <script>
                        !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
                        ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
                        ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
                        for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
                        ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
                        ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";
                        ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;
                        ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");
                        o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;
                        var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
                        ttq.load('{$tiktokPixelId}');ttq.page()}(window,document,'ttq');
                    </script>
                    HTML);
                });
        }
    }
}
