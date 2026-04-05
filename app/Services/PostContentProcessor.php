<?php

namespace App\Services;

use App\Models\Setting;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Support\Str;
use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Stevebauman\Purify\Facades\Purify;

/**
 * Blog post / page HTML pipeline after Filament RichEditor.
 *
 * Order: {@see self::sanitize()} (config `purify.configs.filament_rich_content`) → {@see self::reconstructFilamentGridStyles()}
 * (TipTap grid: trusted `--cols` / `--col-span` from `data-*`) → Filament custom blocks
 * ({@see HasRichContent} + {@see \Filament\Forms\Components\RichEditor\RichContentRenderer::toUnsafeHtml()}) → Phiki → heading anchors.
 * Editor state must stay TipTap HTML in {@see \App\Models\Post::$body_raw}; do not feed {@see \App\Models\Post::$body} back into Purify.
 */
class PostContentProcessor
{
    /**
     * Common language aliases that TipTap may use but don't match Phiki Grammar enum values.
     *
     * @var array<string, string>
     */
    private const LANGUAGE_ALIASES = [
        // Shell
        'bash' => 'shellscript',
        'sh' => 'shellscript',
        'shell' => 'shellscript',
        'zsh' => 'shellscript',
        // Web
        'js' => 'javascript',
        'javascript' => 'javascript',
        'ts' => 'typescript',
        'typescript' => 'typescript',
        'jsx' => 'jsx',
        'tsx' => 'tsx',
        // Python
        'py' => 'python',
        'python3' => 'python',
        // Ruby
        'rb' => 'ruby',
        // Data / Config
        'yml' => 'yaml',
        'env' => 'dotenv',
        'tf' => 'terraform',
        'hcl' => 'hcl',
        'toml' => 'toml',
        // Systems
        'rs' => 'rust',
        'golang' => 'go',
        'cs' => 'csharp',
        'c#' => 'csharp',
        'c++' => 'cpp',
        'kt' => 'kotlin',
        'kts' => 'kotlin',
        'ps1' => 'powershell',
        'cmd' => 'bat',
        // Markup / Docs
        'md' => 'markdown',
        'mdx' => 'mdx',
        'html' => 'html',
        'xml' => 'xml',
        // Templates
        'hbs' => 'handlebars',
        'jinja2' => 'jinja',
        'twig' => 'twig',
        // Scripting / Other
        'coffeescript' => 'coffee',
        'makefile' => 'make',
        'vim' => 'viml',
        'vimscript' => 'viml',
        'objc' => 'objective-c',
        'dockerfile' => 'docker',
        // Plain text
        'plain' => 'txt',
        'plaintext' => 'txt',
        'text' => 'txt',
    ];

    /**
     * Process HTML through the 3-step pipeline: sanitize, highlight, heading anchors.
     */
    public function process(string $html, ?HasRichContent $subject = null): string
    {
        if (trim($html) === '') {
            return $html;
        }

        $html = $this->sanitize($html);
        $html = $this->reconstructFilamentGridStyles($html);
        $html = $this->expandRichEditorCustomBlocks($html, $subject);
        $html = $this->highlightCodeBlocks($html);
        $html = $this->addHeadingAnchors($html);

        return $html;
    }

    /**
     * Replace TipTap `customBlock` placeholders with server-rendered HTML (embeds, callouts, …).
     * Uses {@see \Filament\Forms\Components\RichEditor\RichContentRenderer::toUnsafeHtml()} — trusted output from app block classes only.
     */
    private function expandRichEditorCustomBlocks(string $html, ?HasRichContent $subject): string
    {
        if ($subject === null || ! $subject->hasRichContentAttribute('body')) {
            return $html;
        }

        $attribute = $subject->getRichContentAttribute('body');
        if ($attribute === null || ! filled($attribute->getCustomBlocks())) {
            return $html;
        }

        return $attribute->getRenderer()->content($html)->toUnsafeHtml();
    }

    /**
     * Step 1: Sanitize HTML using HTMLPurifier via stevebauman/purify.
     */
    private function sanitize(string $html): string
    {
        $cleaned = Purify::config('filament_rich_content')->clean($html);

        return is_string($cleaned) ? $cleaned : '';
    }

    /**
     * Re-apply Filament/TipTap grid custom properties after Purify (HTMLPurifier cannot allowlist `--cols` / `--col-span`
     * with this package’s CSS hook order; `data-cols` / `data-col-span` on the divs are preserved by Purify).
     *
     * @see \Filament\Forms\Components\RichEditor\TipTapExtensions\GridExtension
     * @see \Filament\Forms\Components\RichEditor\TipTapExtensions\GridColumnExtension
     */
    private function reconstructFilamentGridStyles(string $html): string
    {
        if (! str_contains($html, 'grid-layout')) {
            return $html;
        }

        $dom = $this->loadHtml($html);
        if ($dom === null) {
            return $html;
        }

        $xpath = new DOMXPath($dom);

        $grids = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' grid-layout ')]");
        if ($grids !== false) {
            foreach ($grids as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }
                $cols = (int) $node->getAttribute('data-cols');
                if ($cols < 1 || $cols > 12) {
                    $cols = 2;
                }
                $node->setAttribute('style', sprintf('--cols: repeat(%d, minmax(0, 1fr))', $cols));
            }
        }

        $columns = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' grid-layout-col ')]");
        if ($columns !== false) {
            foreach ($columns as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }
                $span = (int) $node->getAttribute('data-col-span');
                if ($span < 1 || $span > 12) {
                    $span = 1;
                }
                $node->setAttribute('style', sprintf('--col-span: span %d / span %d', $span, $span));
            }
        }

        return $this->saveHtml($dom);
    }

    /**
     * Step 2: Find <pre><code> blocks and replace with Phiki-highlighted HTML.
     */
    private function highlightCodeBlocks(string $html): string
    {
        if (! str_contains($html, '<code')) {
            return $html;
        }

        $dom = $this->loadHtml($html);
        if ($dom === null) {
            return $html;
        }

        $xpath = new DOMXPath($dom);
        $codeElements = $xpath->query('//pre/code');

        if ($codeElements === false || $codeElements->length === 0) {
            return $html;
        }

        $phiki = new Phiki;
        $themeKey = Setting::get('code_theme', 'GitHub');
        $themesRaw = config('appearance.code_themes');
        if (! is_array($themesRaw)) {
            return $html;
        }

        $rawPair = $themesRaw[$themeKey] ?? $themesRaw['GitHub'] ?? null;
        if (! is_array($rawPair) || ! isset($rawPair[0], $rawPair[1])) {
            return $html;
        }

        $lightName = $rawPair[0];
        $darkName = $rawPair[1];
        if (! is_string($lightName) || ! is_string($darkName)) {
            return $html;
        }

        $lightTheme = Theme::from($lightName);
        $darkTheme = Theme::from($darkName);

        foreach ($codeElements as $codeElement) {
            if (! $codeElement instanceof DOMElement) {
                continue;
            }

            $preElement = $codeElement->parentNode;

            if ($preElement === null || $preElement->parentNode === null) {
                continue;
            }

            $language = $this->extractLanguage($codeElement);
            $codeContent = $codeElement->textContent;

            $grammar = $this->resolveGrammar($language);

            try {
                $highlighted = $phiki->codeToHtml(
                    code: $codeContent,
                    grammar: $grammar,
                    theme: [
                        'light' => $lightTheme,
                        'dark' => $darkTheme,
                    ],
                );
            } catch (\Throwable) {
                $highlighted = $phiki->codeToHtml(
                    code: $codeContent,
                    grammar: Grammar::Txt,
                    theme: [
                        'light' => $lightTheme,
                        'dark' => $darkTheme,
                    ],
                );
            }

            $languageLabel = $language !== '' ? $language : 'text';
            $wrapperHtml = '<div class="code-block" data-language="'.htmlspecialchars($languageLabel).'">'.$highlighted.'</div>';

            $fragment = $dom->createDocumentFragment();
            @$fragment->appendXML($wrapperHtml);

            if ($fragment->hasChildNodes()) {
                $preElement->parentNode->replaceChild($fragment, $preElement);
            }
        }

        return $this->saveHtml($dom);
    }

    /**
     * Step 3: Add id attributes and anchor links to h2 and h3 headings.
     */
    private function addHeadingAnchors(string $html): string
    {
        if (preg_match('/<h[23][\s>]/i', $html) !== 1) {
            return $html;
        }

        $dom = $this->loadHtml($html);
        if ($dom === null) {
            return $html;
        }

        $xpath = new DOMXPath($dom);
        $headings = $xpath->query('//h2|//h3');

        if ($headings === false || $headings->length === 0) {
            return $html;
        }

        /** @var array<string, int> */
        $usedSlugs = [];

        foreach ($headings as $heading) {
            if (! $heading instanceof DOMElement) {
                continue;
            }

            $text = $heading->textContent;
            $slug = Str::slug($text);

            if ($slug === '') {
                continue;
            }

            if (isset($usedSlugs[$slug])) {
                $usedSlugs[$slug]++;
                $slug .= '-'.$usedSlugs[$slug];
            } else {
                $usedSlugs[$slug] = 1;
            }

            $heading->setAttribute('id', $slug);

            $anchor = $dom->createElement('a');
            $anchor->setAttribute('href', '#'.$slug);
            $anchor->setAttribute('class', 'heading-anchor');
            $anchor->setAttribute('aria-label', __('Link to section'));
            $heading->insertBefore($anchor, $heading->firstChild);
        }

        return $this->saveHtml($dom);
    }

    /**
     * Extract language from code element's class attribute (e.g., "language-php").
     */
    private function extractLanguage(\DOMElement $codeElement): string
    {
        $class = $codeElement->getAttribute('class');

        if (preg_match('/language-(\S+)/', $class, $matches) === 1) {
            return strtolower($matches[1]);
        }

        return '';
    }

    /**
     * Resolve a language string to a Phiki Grammar enum case.
     */
    private function resolveGrammar(string $language): Grammar
    {
        if ($language === '') {
            return Grammar::Txt;
        }

        $normalized = strtolower($language);

        if (isset(self::LANGUAGE_ALIASES[$normalized])) {
            $normalized = self::LANGUAGE_ALIASES[$normalized];
        }

        return Grammar::tryFrom($normalized) ?? Grammar::Txt;
    }

    /**
     * Load HTML fragment into DOMDocument with proper UTF-8 handling.
     */
    private function loadHtml(string $html): ?DOMDocument
    {
        $dom = new DOMDocument;
        $wrapped = '<div>'.mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8').'</div>';

        $success = @$dom->loadHTML(
            $wrapped,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        return $success ? $dom : null;
    }

    /**
     * Extract inner HTML from the wrapper div.
     */
    private function saveHtml(DOMDocument $dom): string
    {
        $wrapper = $dom->getElementsByTagName('div')->item(0);

        if ($wrapper === null) {
            return '';
        }

        $result = '';

        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }
}
