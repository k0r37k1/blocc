<aside class="callout callout--{{ $variant }} not-prose my-6 rounded-lg p-4" role="note">
    @if (filled($title))
        <p class="callout__title mb-2 font-semibold">{{ $title }}</p>
    @endif
    <div class="callout__body text-sm leading-relaxed">{!! nl2br(e($body)) !!}</div>
</aside>
