<?php

namespace App\Services;

use App\Models\Setting;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;
use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Stevebauman\Purify\Facades\Purify;

class PostContentProcessor
{
    /**
     * Common language aliases that TipTap may use but don't match Phiki Grammar enum values.
     *
     * @var array<string, string>
     */
    private const LANGUAGE_ALIASES = [
        'bash' => 'shellscript',
        'sh' => 'shellscript',
        'shell' => 'shellscript',
        'js' => 'javascript',
        'ts' => 'typescript',
        'py' => 'python',
        'rb' => 'ruby',
        'yml' => 'yaml',
        'dockerfile' => 'docker',
        'plain' => 'txt',
        'plaintext' => 'txt',
        'text' => 'txt',
    ];

    /**
     * Process HTML through the 3-step pipeline: sanitize, highlight, heading anchors.
     */
    public function process(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        $html = $this->sanitize($html);
        $html = $this->highlightCodeBlocks($html);
        $html = $this->addHeadingAnchors($html);

        return $html;
    }

    /**
     * Step 1: Sanitize HTML using HTMLPurifier via stevebauman/purify.
     */
    private function sanitize(string $html): string
    {
        /** @var string $cleaned */
        $cleaned = Purify::clean($html);

        return $cleaned;
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
        $themes = config('appearance.code_themes');
        $pair = $themes[$themeKey] ?? $themes['GitHub'];
        $lightTheme = Theme::from($pair[0]);
        $darkTheme = Theme::from($pair[1]);

        foreach ($codeElements as $codeElement) {
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
