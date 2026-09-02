<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed a single demo blog post.
     */
    public function run(): void
    {
        $category = Category::where('slug', 'allgemein')->first() ?? Category::first();

        $post = Post::updateOrCreate(
            ['slug' => 'demo-beitrag-willkommen-auf-dem-blog'],
            [
                'title' => 'Demo-Beitrag: Willkommen auf dem Blog',
                'body' => '<p>Willkommen auf meinem Blog! Dies ist ein Demo-Beitrag, um das Layout, die Typografie und die Leserfahrung zu zeigen.</p><p>Hier schreibe ich über <strong>Webentwicklung</strong>, <strong>Design</strong> und die Tools, mit denen ich arbeite — darunter <strong>Laravel</strong>, <strong>Filament</strong> und <strong>Tailwind CSS</strong>.</p><h2>Was dich hier erwartet</h2><p>Kurze, prägnante Artikel mit Fokus auf Qualität statt Quantität. Neue Beiträge erscheinen, wenn es etwas Wertvolles zu teilen gibt.</p><p>Schau gerne öfter vorbei — und wenn du magst, abonniere den Newsletter unten auf der Startseite.</p>',
                'excerpt' => 'Ein Demo-Beitrag: Willkommen auf dem Blog — Webentwicklung, Design und der Stack hinter dieser Seite.',
                'status' => PostStatus::Published,
                'published_at' => now(),
                'category_id' => $category?->id,
                'reading_time' => 1,
                'user_id' => 1,
                'comments_enabled' => true,
                'toc_enabled' => true,
            ],
        );

        $post->tags()->sync(
            Tag::whereIn('slug', ['demo'])->pluck('id')
        );

        if (! $post->hasMedia('featured-image')) {
            $this->attachFeaturedImage($post);
        }
    }

    /**
     * Generate a placeholder image with GD and attach it as featured image.
     */
    private function attachFeaturedImage(Post $post): void
    {
        $colors = [
            [72, 120, 80],    // forest green
            [180, 95, 60],    // terracotta
            [60, 90, 140],    // steel blue
            [140, 110, 70],   // warm brown
            [100, 130, 100],  // sage
            [160, 80, 90],    // dusty rose
            [80, 110, 130],   // slate
        ];

        $color = $colors[$post->id % count($colors)];
        $width = 1200;
        $height = 675;

        $image = imagecreatetruecolor($width, $height);

        if ($image === false) {
            return;
        }

        $bg = imagecolorallocate($image, $color[0], $color[1], $color[2]);
        $lighter = imagecolorallocate($image, min(255, $color[0] + 30), min(255, $color[1] + 30), min(255, $color[2] + 30));

        if ($bg === false || $lighter === false) {
            imagedestroy($image);

            return;
        }

        imagefill($image, 0, 0, $bg);

        // Add subtle diagonal lines for texture
        for ($i = -$height; $i < $width + $height; $i += 40) {
            imageline($image, $i, 0, $i + $height, $height, $lighter);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'seed_img_').'.jpg';
        imagejpeg($image, $tempPath, 85);
        imagedestroy($image);

        $post->addMedia($tempPath)
            ->usingFileName("featured-{$post->slug}.jpg")
            ->toMediaCollection('featured-image');

        $post->update(['featured_image_alt' => "Featured image for {$post->title}"]);
    }
}
