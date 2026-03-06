<?php

namespace Database\Seeders;

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

        $post = Post::create([
            'title' => 'Willkommen auf meinem Blog',
            'slug' => 'willkommen-auf-meinem-blog',
            'body' => '<p>Dies ist mein erster Blogbeitrag. Hier werde ich über Themen wie Webentwicklung, Design und Technologie schreiben.</p><p>Dieser Blog wurde mit <strong>Laravel</strong>, <strong>Filament</strong> und <strong>Tailwind CSS</strong> gebaut — ein moderner Stack für schnelle und elegante Webanwendungen.</p><p>Schau gerne öfter vorbei, es wird sich hier einiges tun!</p>',
            'excerpt' => 'Mein erster Blogbeitrag — über Webentwicklung, Design und den Tech-Stack hinter diesem Blog.',
            'status' => \App\Enums\PostStatus::Published,
            'published_at' => now(),
            'category_id' => $category?->id,
            'reading_time' => 1,
            'user_id' => 1,
        ]);

        $post->tags()->attach(
            Tag::whereIn('slug', ['demo'])->pluck('id')
        );

        $this->attachFeaturedImage($post);
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
