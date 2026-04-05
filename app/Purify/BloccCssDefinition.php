<?php

namespace App\Purify;

use HTMLPurifier_AttrDef_Enum;
use HTMLPurifier_CSSDefinition;
use Stevebauman\Purify\Definitions\CssDefinition;

/**
 * Aligns CSS validation with Filament RichEditor {@see \Tiptap\Extensions\TextAlign} (start/end keywords).
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
