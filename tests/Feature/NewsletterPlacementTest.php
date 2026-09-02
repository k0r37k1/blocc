<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterPlacementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setMany([
            'newsletter_enabled' => '1',
            'newsletter_placement' => 'article',
        ]);
    }

    public function test_homepage_does_not_show_newsletter_form(): void
    {
        Post::factory()->published()->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertDontSee('id="footer-newsletter"', false)
            ->assertDontSee('id="article-newsletter"', false);
    }

    public function test_article_shows_newsletter_when_placement_is_article(): void
    {
        $post = Post::factory()->published()->create();

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('id="article-newsletter"', false)
            ->assertDontSee('id="footer-newsletter"', false);
    }

    public function test_footer_shows_newsletter_when_placement_is_footer(): void
    {
        Setting::set('newsletter_placement', 'footer');

        Post::factory()->published()->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('id="footer-newsletter"', false);
    }

    public function test_article_hides_inline_newsletter_when_placement_is_footer(): void
    {
        Setting::set('newsletter_placement', 'footer');

        $post = Post::factory()->published()->create();

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertDontSee('id="article-newsletter"', false)
            ->assertSee('id="footer-newsletter"', false);
    }
}
