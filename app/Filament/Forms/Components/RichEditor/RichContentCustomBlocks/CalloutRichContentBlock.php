<?php

namespace App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class CalloutRichContentBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return 'callout';
    }

    public static function getLabel(): string
    {
        return __('Rich editor: Callout');
    }

    public static function getPreviewLabel(array $config): string
    {
        $title = $config['title'] ?? '';
        if (is_string($title) && $title !== '') {
            return Str::limit($title, 72);
        }

        $body = $config['body'] ?? '';

        return is_string($body) && $body !== ''
            ? Str::limit(strip_tags($body), 72)
            : static::getLabel();
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalDescription(__('Use callouts for tips, warnings, or short highlighted notes.'))
            ->schema([
                Select::make('variant')
                    ->label(__('Callout type'))
                    ->options([
                        'info' => __('Callout variant: Information'),
                        'warning' => __('Callout variant: Warning'),
                        'success' => __('Callout variant: Success'),
                        'tip' => __('Callout variant: Tip'),
                    ])
                    ->default('info')
                    ->required(),
                TextInput::make('title')
                    ->label(__('Title (optional)'))
                    ->maxLength(180),
                Textarea::make('body')
                    ->label(__('Text'))
                    ->rows(5)
                    ->required(),
            ]);
    }

    public static function toPreviewHtml(array $config): string
    {
        return view('filament.forms.components.rich-editor.rich-content-custom-blocks.callout-rich-content.preview', [
            'variant' => $config['variant'] ?? 'info',
            'title' => $config['title'] ?? '',
            'body' => $config['body'] ?? '',
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $data
     */
    public static function toHtml(array $config, array $data): string
    {
        $variant = $config['variant'] ?? 'info';
        if (! is_string($variant) || ! in_array($variant, ['info', 'warning', 'success', 'tip'], true)) {
            $variant = 'info';
        }

        $title = $config['title'] ?? '';
        $title = is_string($title) ? $title : '';

        $body = $config['body'] ?? '';
        $body = is_string($body) ? $body : '';

        return view('filament.forms.components.rich-editor.rich-content-custom-blocks.callout-rich-content.index', [
            'variant' => $variant,
            'title' => $title,
            'body' => $body,
        ])->render();
    }
}
