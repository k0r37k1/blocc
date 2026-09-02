@props([
    'url' => null,
])

<button
    type="button"
    {{ $attributes->class('copy-link-btn') }}
    x-data="copyToClipboard"
    data-url="{{ $url ?? url()->current() }}"
    data-copy-label="{{ __('Copy link') }}"
    data-copied-label="{{ __('Link copied!') }}"
    x-on:click="copy()"
    x-bind:class="{ 'copied': copied }"
    x-bind:aria-label="copied ? copiedLabel : copyLabel"
>
    <x-icons.copy class="h-4 w-4 shrink-0" />
    <span class="copy-tooltip" x-text="copied ? copiedLabel : copyLabel" aria-hidden="true"></span>
</button>
