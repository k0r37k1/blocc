<?php

use App\Purify\BloccCssDefinition;
use App\Purify\BloccHtmlDefinition;

/**
 * HTMLPurifier rules aligned with Filament v5 {@see \Filament\Forms\Components\RichEditor} (TipTap).
 *
 * Toolbar (PostForm / PageForm): bold, italic, underline, strike, link, textColor, h1, h2, h3,
 * blockquote, codeBlock, bulletList, orderedList, table, horizontalRule, details, highlight, small,
 * lead, attachFiles, undo, redo.
 *
 * Still optional in toolbar (Purify ready): textAlign (style text-align on p/headings), subscript,
 * superscript, grid blocks.
 *
 * Pipeline: Filament saves TipTap HTML → {@see \App\Services\PostContentProcessor::sanitize()}
 * (this config) → Phiki code blocks → heading anchors. Public HTML is {@see \App\Models\Post::$body};
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
                // Lead block + TipTap details body
                'div[class|data-type]',
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
            // TipTap textColor uses inline `color`; images use width/height; textAlign uses text-align.
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
