<?php

use App\Purify\BloccCssDefinition;
use App\Purify\BloccHtmlDefinition;

/**
 * HTMLPurifier rules aligned with Filament v5 {@see \Filament\Forms\Components\RichEditor} (TipTap).
 *
 * Toolbar ({@see \App\Filament\RichEditor\BodyToolbar} on PostForm / PageForm): bold, italic, underline,
 * strike | subscript, superscript | link, textColor | headings h1–h6 (dropdown) | alignStart–alignJustify |
 * bulletList, orderedList (dropdown) | blockquote, codeBlock | table, horizontalRule, details | grid, gridDelete |
 * highlight, small, lead | customBlocks (video embed + callout), attachFiles | undo, redo.
 *
 * Custom blocks are stored as `div[data-type="customBlock"]` until {@see PostContentProcessor} expands them.
 *
 * Pipeline: Filament saves TipTap HTML → {@see \App\Services\PostContentProcessor::sanitize()} (this config) →
 * {@see \App\Services\PostContentProcessor::reconstructFilamentGridStyles()} (grid `--cols` / `--col-span`) → …
 * → Phiki code blocks → heading anchors. Public HTML is {@see \App\Models\Post::$body};
 * the editor loads {@see \App\Models\Post::$body_raw} when set (never run Phiki output through Purify).
 *
 * Custom attributes: {@see BloccHtmlDefinition}.
 */
return [

    'default' => 'filament_rich_content',

    'configs' => [

        'filament_rich_content' => [
            'Core.Encoding' => 'utf-8',
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => implode(',', [
                // Paragraphs may carry TipTap text-align via style when textAlign is enabled in Filament.
                'p[class|style]',
                'br',
                'hr',
                'h1[class|style]',
                'h2[class|style]',
                'h3[class|style]',
                'h4[class|style]',
                'h5[class|style]',
                'h6[class|style]',
                'strong',
                'em',
                'u',
                's',
                'sub',
                'sup',
                'small',
                'mark',
                'span[class|style|data-color]',
                'a[href|title|target|rel|class|aria-label]',
                'ul',
                'ol',
                'li',
                'blockquote',
                'pre[class]',
                'code[class]',
                // Lead block, TipTap details body, grid (optional), Filament customBlock placeholder
                'div[class|style|data-type|data-id|data-config|data-cols|data-from-breakpoint|data-col-span]',
                // Callout block output
                'aside[class|role]',
                'img[src|alt|width|height|data-id|loading|class|style]',
                'table',
                'thead',
                'tbody',
                'tr',
                'th[colspan|rowspan|data-colwidth]',
                'td[colspan|rowspan|data-colwidth]',
                'figure',
                'figcaption',
                'dl',
                'dt',
                'dd',
                'details',
                'summary',
            ]),
            // TipTap: textColor → color; textAlign → text-align. Grid `--cols` / `--col-span` are re-applied in PostContentProcessor from data-* (see BloccCssDefinition).
            'CSS.AllowedProperties' => 'color,text-align,width,height,max-width,max-height',
            'HTML.ForbiddenElements' => 'script,style,iframe,form,input,textarea,select,button',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => false,
        ],

    ],

    'definitions' => BloccHtmlDefinition::class,

    'css-definitions' => BloccCssDefinition::class,

    'serializer' => [
        'driver' => env('CACHE_STORE', env('CACHE_DRIVER', 'file')),
        'cache' => \Stevebauman\Purify\Cache\CacheDefinitionCache::class,
    ],

];
