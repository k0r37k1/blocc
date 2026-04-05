<?php

namespace Tests\Unit;

use App\Services\PostContentProcessor;
use Tests\TestCase;

class PostContentProcessorRichEditorHtmlTest extends TestCase
{
    private function processed(string $html): string
    {
        $out = app(PostContentProcessor::class)->process($html);
        $this->assertIsString($out);

        return $out;
    }

    public function test_preserves_lead_div_from_filament_rich_editor(): void
    {
        $html = '<div class="lead"><p>Einleitungstext</p></div>';

        $out = $this->processed($html);

        $this->assertStringContainsString('class="lead"', $out);
        $this->assertStringContainsString('Einleitungstext', $out);
    }

    public function test_preserves_details_summary_and_details_content_div(): void
    {
        $html = '<details><summary>Titel</summary><div data-type="detailsContent"><p>Inhalt</p></div></details>';

        $out = $this->processed($html);

        $this->assertStringContainsString('<details>', $out);
        $this->assertStringContainsString('<summary>', $out);
        $this->assertStringContainsString('data-type="detailsContent"', $out);
        $this->assertStringContainsString('Inhalt', $out);
    }

    public function test_preserves_table_cell_colspan_rowspan_and_data_colwidth(): void
    {
        $html = '<table><tbody><tr><th colspan="2" rowspan="1" data-colwidth="120,240">A</th><td rowspan="2" data-colwidth="100">B</td></tr></tbody></table>';

        $out = $this->processed($html);

        $this->assertStringContainsString('colspan="2"', $out);
        $this->assertStringContainsString('rowspan="2"', $out);
        $this->assertStringContainsString('data-colwidth="120,240"', $out);
    }

    public function test_preserves_embedded_image_data_id_loading_and_dimensions(): void
    {
        $html = '<p><img src="https://example.test/storage/1/foo.jpg" alt="x" data-id="42" loading="lazy" width="120" height="80"></p>';

        $out = $this->processed($html);

        $this->assertStringContainsString('data-id="42"', $out);
        $this->assertStringContainsString('loading="lazy"', $out);
        $this->assertStringContainsString('width="120"', $out);
        $this->assertStringContainsString('height="80"', $out);
    }

    public function test_preserves_highlight_mark(): void
    {
        $html = '<p><mark>Hervorgehoben</mark></p>';

        $out = $this->processed($html);

        $this->assertStringContainsString('<mark>', $out);
        $this->assertStringContainsString('Hervorgehoben', $out);
    }

    public function test_preserves_text_align_start_and_end_for_filament_text_align(): void
    {
        $html = '<p style="text-align: end">R</p><h2 style="text-align: start">T</h2>';

        $out = $this->processed($html);

        $compact = preg_replace('/\s+/', '', $out);
        $this->assertIsString($compact);
        $this->assertStringContainsString('text-align:end', $compact);
        $this->assertStringContainsString('text-align:start', $compact);
    }

    public function test_preserves_percentage_width_style_on_images(): void
    {
        $html = '<p><img src="https://example.test/a.jpg" alt="x" style="width: 50%"></p>';

        $out = $this->processed($html);

        $this->assertMatchesRegularExpression('/width:\s*50%/', $out);
    }

    public function test_preserves_h1_heading(): void
    {
        $html = '<h1 class="foo">Titel</h1>';

        $out = $this->processed($html);

        $this->assertStringContainsString('<h1', $out);
        $this->assertStringContainsString('Titel', $out);
    }

    public function test_preserves_filament_text_color_span(): void
    {
        $html = '<p><span style="color:#ef4444" data-color="red-600">Rot</span></p>';

        $out = $this->processed($html);

        $this->assertStringContainsString('data-color="red-600"', $out);
        $this->assertStringContainsString('Rot', $out);
        $this->assertMatchesRegularExpression('/color:\s*#ef4444/i', $out);
    }

    public function test_preserves_subscript_and_superscript(): void
    {
        $html = '<p>H<sub>2</sub>O and E=mc<sup>2</sup></p>';

        $out = $this->processed($html);

        $this->assertStringContainsString('<sub>', $out);
        $this->assertStringContainsString('<sup>', $out);
    }

    public function test_preserves_h4_heading(): void
    {
        $html = '<h4 class="k">Section</h4>';

        $out = $this->processed($html);

        $this->assertStringContainsString('<h4', $out);
        $this->assertStringContainsString('Section', $out);
    }

    public function test_preserves_filament_grid_layout_and_column_custom_properties(): void
    {
        $html = '<div class="grid-layout" data-cols="2" data-from-breakpoint="lg" style="--cols: repeat(2, minmax(0, 1fr))">'
            .'<div class="grid-layout-col" data-col-span="1" style="--col-span: span 1 / span 1"><p>A</p></div>'
            .'<div class="grid-layout-col" data-col-span="1" style="--col-span: span 1 / span 1"><p>B</p></div>'
            .'</div>';

        $out = $this->processed($html);

        $this->assertStringContainsString('grid-layout', $out);
        $this->assertStringContainsString('data-from-breakpoint="lg"', $out);
        $this->assertMatchesRegularExpression('/--cols:\s*repeat\(\s*2\s*,\s*minmax\(\s*0\s*,\s*1fr\s*\)\s*\)/', $out);
        $this->assertMatchesRegularExpression('/--col-span:\s*span\s+1\s*\/\s*span\s+1/', $out);
    }
}
