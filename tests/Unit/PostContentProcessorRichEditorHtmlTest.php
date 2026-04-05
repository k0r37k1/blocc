<?php

namespace Tests\Unit;

use App\Services\PostContentProcessor;
use Tests\TestCase;

class PostContentProcessorRichEditorHtmlTest extends TestCase
{
    public function test_preserves_lead_div_from_filament_rich_editor(): void
    {
        $html = '<div class="lead"><p>Einleitungstext</p></div>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('class="lead"', $out);
        $this->assertStringContainsString('Einleitungstext', $out);
    }

    public function test_preserves_details_summary_and_details_content_div(): void
    {
        $html = '<details><summary>Titel</summary><div data-type="detailsContent"><p>Inhalt</p></div></details>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('<details>', $out);
        $this->assertStringContainsString('<summary>', $out);
        $this->assertStringContainsString('data-type="detailsContent"', $out);
        $this->assertStringContainsString('Inhalt', $out);
    }

    public function test_preserves_table_cell_colspan_rowspan_and_data_colwidth(): void
    {
        $html = '<table><tbody><tr><th colspan="2" rowspan="1" data-colwidth="120,240">A</th><td rowspan="2" data-colwidth="100">B</td></tr></tbody></table>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('colspan="2"', $out);
        $this->assertStringContainsString('rowspan="2"', $out);
        $this->assertStringContainsString('data-colwidth="120,240"', $out);
    }

    public function test_preserves_embedded_image_data_id_loading_and_dimensions(): void
    {
        $html = '<p><img src="https://example.test/storage/1/foo.jpg" alt="x" data-id="42" loading="lazy" width="120" height="80"></p>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('data-id="42"', $out);
        $this->assertStringContainsString('loading="lazy"', $out);
        $this->assertStringContainsString('width="120"', $out);
        $this->assertStringContainsString('height="80"', $out);
    }

    public function test_preserves_highlight_mark(): void
    {
        $html = '<p><mark>Hervorgehoben</mark></p>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('<mark>', $out);
        $this->assertStringContainsString('Hervorgehoben', $out);
    }

    public function test_preserves_text_align_start_and_end_for_filament_text_align(): void
    {
        $html = '<p style="text-align: end">R</p><h2 style="text-align: start">T</h2>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('text-align:end', preg_replace('/\s+/', '', $out));
        $this->assertStringContainsString('text-align:start', preg_replace('/\s+/', '', $out));
    }

    public function test_preserves_percentage_width_style_on_images(): void
    {
        $html = '<p><img src="https://example.test/a.jpg" alt="x" style="width: 50%"></p>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertMatchesRegularExpression('/width:\s*50%/', $out);
    }

    public function test_preserves_h1_heading(): void
    {
        $html = '<h1 class="foo">Titel</h1>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('<h1', $out);
        $this->assertStringContainsString('Titel', $out);
    }

    public function test_preserves_filament_text_color_span(): void
    {
        $html = '<p><span style="color:#ef4444" data-color="red-600">Rot</span></p>';

        $out = app(PostContentProcessor::class)->process($html);

        $this->assertStringContainsString('data-color="red-600"', $out);
        $this->assertStringContainsString('Rot', $out);
        $this->assertMatchesRegularExpression('/color:\s*#ef4444/i', $out);
    }
}
