<aside id="cookies-policy" class="cookies cookies--no-js" data-text="{{ json_encode(__('cookieConsent::cookies.details')) }}">

    {{-- Trigger pill button (bottom-left) --}}
    <button class="cookies__trigger" type="button" aria-label="@lang('cookieConsent::cookies.title')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z"/>
        </svg>
        <span>@lang('cookieConsent::cookies.title')</span>
    </button>

    {{-- Panel (visibility:hidden keeps it measurable for JS) --}}
    <div class="cookies__alert">
        <div class="cookies__container">
            <div class="cookies__wrapper">
                <h2 class="cookies__title">@lang('cookieConsent::cookies.title')</h2>
                <div class="cookies__intro">
                    <p>@lang('cookieConsent::cookies.intro')</p>
                    @php $policyUrl = \App\Models\Setting::get('cookie_policy_url'); @endphp
                    @if($policyUrl)
                        <p>{!! __('cookieConsent::cookies.link', ['url' => $policyUrl]) !!}</p>
                    @endif
                </div>
                <div class="cookies__actions">
                    @cookieconsentbutton(action: 'accept.essentials', label: __('cookieConsent::cookies.essentials'), attributes: ['class' => 'cookiesBtn cookiesBtn--essentials'])
                    @cookieconsentbutton(action: 'accept.all', label: __('cookieConsent::cookies.all'), attributes: ['class' => 'cookiesBtn cookiesBtn--accept'])
                </div>
            </div>
        </div>
        <a href="#cookies-policy-customize" class="cookies__btn cookies__btn--customize">
            <span>@lang('cookieConsent::cookies.customize')</span>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M14.7559 11.9782C15.0814 11.6527 15.0814 11.1251 14.7559 10.7996L10.5893 6.63297C10.433 6.47669 10.221 6.3889 10 6.38889C9.77899 6.38889 9.56703 6.47669 9.41075 6.63297L5.24408 10.7996C4.91864 11.1251 4.91864 11.6527 5.24408 11.9782C5.56951 12.3036 6.09715 12.3036 6.42259 11.9782L10 8.40074L13.5774 11.9782C13.9028 12.3036 14.4305 12.3036 14.7559 11.9782Z" fill="currentColor"/>
            </svg>
        </a>
        <div class="cookies__expandable cookies__expandable--custom" id="cookies-policy-customize">
            <form action="{{ route('cookieconsent.accept.configuration') }}" method="post" class="cookies__customize">
                @csrf
                <div class="cookies__sections">
                    @foreach($cookies->getCategories() as $category)
                    <div class="cookies__section">
                        <label for="cookies-policy-check-{{ $category->key() }}" class="cookies__category">
                            @if ($category->key() === 'essentials')
                                <input type="hidden" name="categories[]" value="{{ $category->key() }}" />
                                <input type="checkbox" name="categories[]" value="{{ $category->key() }}" id="cookies-policy-check-{{ $category->key() }}" checked disabled />
                            @else
                                <input type="checkbox" name="categories[]" value="{{ $category->key() }}" id="cookies-policy-check-{{ $category->key() }}" />
                            @endif
                            <span class="cookies__box">
                                <strong class="cookies__label">{{ $category->title }}</strong>
                            </span>
                            @if($category->description)
                                <p class="cookies__info">{{ $category->description }}</p>
                            @endif
                        </label>
                        <div class="cookies__expandable" id="cookies-policy-{{ $category->key() }}">
                            <ul class="cookies__definitions">
                                @foreach($category->getCookies() as $cookie)
                                <li class="cookies__cookie">
                                    <p class="cookies__name">{{ $cookie->name }}</p>
                                    <p class="cookies__duration">{{ Carbon\Carbon::now()->diffForHumans(Carbon\Carbon::now()->addMinutes($cookie->duration), true) }}</p>
                                    @if($cookie->description)
                                        <p class="cookies__description">{{ $cookie->description }}</p>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <a href="#cookies-policy-{{ $category->key() }}" class="cookies__details">@lang('cookieConsent::cookies.details.more')</a>
                    </div>
                    @endforeach
                </div>
                <div class="cookies__save">
                    <button type="submit" class="cookiesBtn cookiesBtn--save">@lang('cookieConsent::cookies.save')</button>
                </div>
            </form>
        </div>
    </div>

</aside>

<script data-cookie-consent>
    {!! file_get_contents(LCC_ROOT . '/dist/script.js') !!}
</script>

<script data-cookie-consent-trigger>
(function () {
    var aside = document.getElementById('cookies-policy');
    var trigger = aside && aside.querySelector('.cookies__trigger');
    if (!aside || !trigger) return;

    function openPanel() { aside.classList.add('cookies--panel-open'); }
    function closePanel() { aside.classList.remove('cookies--panel-open'); }

    // Auto-open if consent hasn't been given yet, or if re-opening via footer "Cookies" link after reload
    if (@json($autoOpen) || sessionStorage.getItem('openCookies') === '1') {
        sessionStorage.removeItem('openCookies');
        openPanel();
    }

    trigger.addEventListener('click', function () {
        aside.classList.contains('cookies--panel-open') ? closePanel() : openPanel();
    });
})();
</script>

<style data-cookie-consent>
/* ── Cookie Consent – bottom-left button + panel ───────────────────── */
#cookies-policy {
    position: fixed;
    bottom: 1.5rem;
    left: 1.5rem;
    z-index: 9999;
    font-family: var(--font-sans, ui-sans-serif, system-ui, sans-serif);
    font-size: 0.9rem;
    line-height: 1.6;
}

#cookies-policy.cookies--no-js { display: none; }

/* ── Trigger pill button ────────────────────────────────────────────── */
.cookies__trigger {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem 0.5rem 0.75rem;
    background: var(--color-bg, #fff);
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 2rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    color: var(--color-muted, #6b7280);
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: box-shadow 0.2s, color 0.15s;
    white-space: nowrap;
    font-family: inherit;
}
.dark .cookies__trigger {
    background: var(--color-card, #171717);
    border-color: rgba(255,255,255,0.08);
    box-shadow: 0 4px 16px rgba(0,0,0,0.4);
}
.cookies__trigger:hover { color: var(--color-accent, #15803d); box-shadow: 0 6px 20px rgba(0,0,0,0.14); }
.cookies--panel-open .cookies__trigger { display: none; }

/* ── Panel – absolutely positioned so it doesn't affect trigger layout ── */
.cookies__alert {
    position: absolute;
    bottom: 0;
    left: 0;
    width: min(28rem, calc(100vw - 3rem));
    max-height: calc(100vh - 3rem);
    overflow-y: auto;
    background: var(--color-bg, #fff);
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 0.875rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
    visibility: hidden;
    opacity: 0;
    pointer-events: none;
    transform: translateY(0.5rem);
    transition: opacity 0.25s ease, transform 0.25s ease, visibility 0s linear 0.25s;
}
.dark .cookies__alert {
    background: var(--color-card, #171717);
    border-color: rgba(255,255,255,0.08);
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
.cookies--panel-open .cookies__alert {
    visibility: visible;
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.cookies__container { padding: 1.25rem 1.25rem 0.875rem; }
/* Neutralize the JS-driven hide animation — container always stays visible */
.cookies__container,
.cookies__container--hide {
    height: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
    overflow: visible !important;
}
.cookies__wrapper { display: flex; flex-direction: column; gap: 0.625rem; }

.cookies__title { font-size: 0.9375rem; font-weight: 600; margin: 0; }

.cookies__intro p { margin: 0; color: var(--color-muted, #6b7280); font-size: 0.8125rem; }
.cookies__intro p + p { margin-top: 0.25rem; }
.cookies__intro a { color: var(--color-accent, #15803d); text-decoration: underline; text-underline-offset: 2px; }

.cookies__actions { display: flex; flex-direction: column; gap: 0.375rem; padding-top: 0.125rem; }

/* ── Buttons ────────────────────────────────────────────────────────── */
.cookiesBtn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: opacity 0.15s;
    text-decoration: none;
    white-space: nowrap;
    font-family: inherit;
    width: 100%;
}
.cookiesBtn--accept { background: var(--color-accent-bg, var(--color-accent, #15803d)); color: #fff; }
.cookiesBtn--accept:hover { opacity: 0.88; }
.cookiesBtn--essentials { background: transparent; color: var(--color-muted, #6b7280); border: 1px solid rgba(0,0,0,0.12); }
.dark .cookiesBtn--essentials { border-color: rgba(255,255,255,0.1); color: #9ca3af; }
.cookiesBtn--essentials:hover { background: rgba(0,0,0,0.04); }
.dark .cookiesBtn--essentials:hover { background: rgba(255,255,255,0.06); }
.cookiesBtn--save { background: var(--color-accent-bg, var(--color-accent, #15803d)); color: #fff; }
.cookiesBtn--save:hover { opacity: 0.88; }

/* ── Customize toggle ───────────────────────────────────────────────── */
.cookies__btn--customize {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    width: 100%;
    padding: 0.5rem 1.25rem;
    font-size: 0.8rem;
    color: var(--color-muted, #6b7280);
    text-decoration: none;
    border-top: 1px solid rgba(0,0,0,0.06);
    transition: color 0.15s;
    cursor: pointer;
    font-family: inherit;
}
.dark .cookies__btn--customize { border-top-color: rgba(255,255,255,0.06); }
.cookies__btn--customize:hover { color: var(--color-accent, #15803d); }
.cookies__btn--customize svg { transition: transform 0.3s ease; }
.cookies--show .cookies__btn--customize svg { transform: rotate(180deg); }

/* ── Expandable – JS controls height via inline styles ──────────────── */
.cookies__expandable { display: block; height: 0; opacity: 0; overflow: hidden; visibility: hidden; transition: height 0.3s ease-out, opacity 0.3s ease-out, visibility 0s linear 0.3s; }
.cookies__expandable--open { height: auto; opacity: 1; overflow-y: auto; visibility: visible; transition: height 0.3s ease-out, opacity 0.3s ease-out; }

/* ── Category sections ──────────────────────────────────────────────── */
.cookies__customize { padding: 0.75rem 1.25rem 1rem; }
.cookies__sections { display: flex; flex-direction: column; gap: 0.5rem; }
.cookies__section {
    border: 1px solid rgba(0,0,0,0.07);
    border-radius: 0.5rem;
    padding: 0.75rem 0.875rem;
    background: rgba(0,0,0,0.015);
}
.dark .cookies__section { border-color: rgba(255,255,255,0.07); background: rgba(255,255,255,0.025); }

.cookies__category { display: flex; align-items: flex-start; gap: 0.625rem; cursor: pointer; }
.cookies__category input[type="checkbox"] {
    appearance: none; -webkit-appearance: none;
    width: 1.0625rem; height: 1.0625rem; min-width: 1.0625rem;
    border: 1.5px solid rgba(0,0,0,0.2); border-radius: 0.25rem;
    background: #fff; cursor: pointer; margin-top: 0.125rem;
    transition: background 0.15s, border-color 0.15s;
}
.dark .cookies__category input[type="checkbox"] { background: #262626; border-color: rgba(255,255,255,0.2); }
.cookies__category input[type="checkbox"]:checked {
    background: var(--color-accent-bg, var(--color-accent, #15803d));
    border-color: var(--color-accent-bg, var(--color-accent, #15803d));
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12.207 4.793a1 1 0 0 1 0 1.414l-5 5a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L6.5 9.086l4.293-4.293a1 1 0 0 1 1.414 0z'/%3E%3C/svg%3E");
    background-size: 100%; background-position: center; background-repeat: no-repeat;
}
.cookies__category input[type="checkbox"]:disabled { opacity: 0.5; cursor: not-allowed; }
.cookies__box { display: flex; flex-direction: column; flex: 1; }
.cookies__label { font-size: 0.8125rem; font-weight: 600; }
.cookies__info { margin: 0.2rem 0 0; font-size: 0.75rem; color: var(--color-muted, #6b7280); line-height: 1.5; }

/* ── Cookie definitions ─────────────────────────────────────────────── */
.cookies__definitions { list-style: none; margin: 0.5rem 0 0; padding: 0; display: flex; flex-direction: column; gap: 0.3rem; }
.cookies__cookie {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 0.1rem 0.5rem; padding: 0.4rem 0.625rem;
    background: rgba(0,0,0,0.03); border-radius: 0.3rem; font-size: 0.75rem;
}
.dark .cookies__cookie { background: rgba(255,255,255,0.04); }
.cookies__name { font-weight: 600; margin: 0; }
.cookies__duration { margin: 0; color: var(--color-muted, #6b7280); text-align: right; }
.cookies__description { margin: 0.2rem 0 0; grid-column: 1 / -1; color: var(--color-muted, #6b7280); line-height: 1.4; }
.cookies__details { display: inline-block; margin-top: 0.4rem; font-size: 0.75rem; color: var(--color-accent, #15803d); text-decoration: none; cursor: pointer; }
.cookies__details:hover { text-decoration: underline; text-underline-offset: 2px; }

.cookies__save { padding-top: 0.75rem; }
.cookies__save .cookiesBtn { width: 100%; }

/* ── Mobile ──────────────────────────────────────────────────────────── */
@media (max-width: 480px) {
    #cookies-policy { left: 1rem; bottom: 1rem; }
    .cookies__alert { width: calc(100vw - 2rem); }
}
</style>
