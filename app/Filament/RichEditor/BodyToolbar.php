<?php

namespace App\Filament\RichEditor;

use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;

final class BodyToolbar
{
    /**
     * Shared Post/Page body {@see \Filament\Forms\Components\RichEditor} toolbar: categories separated as
     * Filament toolbar groups (gap + structure). Headings and lists use dropdowns to save space.
     * Keep `config/purify.php` and `App\Purify\*Definition` in sync with enabled tools.
     *
     * @return array<int, array<int, string|ToolbarButtonGroup>>
     */
    public static function buttons(): array
    {
        return [
            ['bold', 'italic', 'underline', 'strike'],
            ['subscript', 'superscript'],
            ['link', 'textColor'],
            [
                ToolbarButtonGroup::make(__('Rich editor: headings menu'), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])
                    ->textualButtons(),
            ],
            ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
            [
                ToolbarButtonGroup::make(__('Rich editor: lists menu'), ['bulletList', 'orderedList'])
                    ->textualButtons(),
            ],
            ['blockquote', 'codeBlock'],
            ['table', 'horizontalRule', 'details'],
            ['grid', 'gridDelete'],
            ['highlight', 'small', 'lead'],
            ['customBlocks', 'attachFiles'],
            ['undo', 'redo'],
        ];
    }
}
