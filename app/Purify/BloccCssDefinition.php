<?php

namespace App\Purify;

use HTMLPurifier_AttrDef_Enum;
use HTMLPurifier_CSSDefinition;
use Stevebauman\Purify\Definitions\CssDefinition;

/**
 * Aligns CSS validation with Filament RichEditor {@see \Tiptap\Extensions\TextAlign} (start/end keywords).
 *
 * TipTap grid uses `--cols` / `--col-span` in inline styles; stevebauman/purify applies this class only after
 * the CSS definition is already filtered against {@see config('purify.configs.filament_rich_content.CSS.AllowedProperties')},
 * so those custom properties cannot be allowlisted there. Instead {@see \App\Services\PostContentProcessor}
 * re-applies safe values from `data-cols` / `data-col-span` after Purify.
 */
class BloccCssDefinition implements CssDefinition
{
    public static function apply(HTMLPurifier_CSSDefinition $definition): void
    {
        $definition->info['text-align'] = new HTMLPurifier_AttrDef_Enum(
            ['left', 'right', 'center', 'justify', 'start', 'end'],
            false,
        );
    }
}
