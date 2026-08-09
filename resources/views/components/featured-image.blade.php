@props([
    'media',
    'alt' => '',
    'class' => '',
    'lazy' => true,
    'priority' => false,
    'width' => 800,
    'height' => 450,
    'conversion' => 'medium',
])

@php
    $dominantColor = $media->getCustomProperty('dominant_color');
    $fallbackSrc = $media->hasGeneratedConversion($conversion)
        ? $media->getUrl($conversion)
        : $media->getAvailableUrl([$conversion, 'thumbnail']);
    $style = collect([
        $dominantColor ? "background-color: {$dominantColor}" : null,
    ])->filter()->implode('; ');

    $attributes = [
        'alt' => $alt,
        'class' => $class,
        'width' => (string) $width,
        'height' => (string) $height,
    ];

    if ($style !== '') {
        $attributes['style'] = $style;
    }

    if ($priority) {
        $attributes['fetchpriority'] = 'high';
        $attributes['loading'] = 'eager';
        $attributes['decoding'] = 'async';
    } elseif ($lazy) {
        $attributes['loading'] = 'lazy';
        $attributes['decoding'] = 'async';
    }

    $img = $media->img($conversion, $attributes);

    if ($lazy && ! $priority) {
        $img = $img->lazy();
    }
@endphp

@if ($media->hasResponsiveImages($conversion))
    {!! $img !!}
@else
    <img
        src="{{ $fallbackSrc }}"
        alt="{{ $alt }}"
        class="{{ $class }}"
        width="{{ $width }}"
        height="{{ $height }}"
        @if ($priority) fetchpriority="high" loading="eager" decoding="async" @elseif ($lazy) loading="lazy" decoding="async" @endif
        @if ($style !== '') style="{{ $style }}" @endif
    >
@endif
