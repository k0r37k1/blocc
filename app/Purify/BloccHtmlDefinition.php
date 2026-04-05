<?php

namespace App\Purify;

use HTMLPurifier_HTMLDefinition;
use Stevebauman\Purify\Definitions\Definition;
use Stevebauman\Purify\Definitions\Html5Definition;

/**
 * HTML element attributes used by Filament v5 RichEditor (TipTap) but not in HTMLPurifier’s stock HTML4 config.
 *
 * Filament textColor uses `span` + `data-color` (+ inline `color` in `style`, whitelisted in purify.php).
 * Grid layouts may need further attributes if enabled in the toolbar.
 *
 * @see config/purify.php
 */
class BloccHtmlDefinition implements Definition
{
    public static function apply(HTMLPurifier_HTMLDefinition $definition): void
    {
        Html5Definition::apply($definition);

        // Filament TipTap textColor mark
        $definition->addAttribute('span', 'data-color', 'Text');

        // Accessible links (editor + optional heading anchors in stored HTML)
        $definition->addAttribute('a', 'aria-label', 'Text');

        // TipTap details block body (Filament DetailsContentExtension)
        $definition->addAttribute('div', 'data-type', 'Text');

        // TipTap / ProseMirror table column widths
        $definition->addAttribute('td', 'data-colwidth', 'Text');
        $definition->addAttribute('th', 'data-colwidth', 'Text');

        // Filament embedded media reference (ImageExtension)
        $definition->addAttribute('img', 'data-id', 'Text');
        $definition->addAttribute('img', 'loading', 'Enum#lazy,eager,auto');
    }
}
