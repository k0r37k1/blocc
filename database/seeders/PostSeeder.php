<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed sample blog posts: 5 published, 2 drafts with categories and tags.
     */
    public function run(): void
    {
        $categoryIds = Category::pluck('id');

        $posts = Post::factory()
            ->count(5)
            ->published()
            ->sequence(fn ($sequence) => ['category_id' => $categoryIds[$sequence->index % $categoryIds->count()]])
            ->create();

        $drafts = Post::factory()
            ->count(2)
            ->draft()
            ->sequence(fn ($sequence) => ['category_id' => $categoryIds->random()])
            ->create();

        $allPosts = $posts->merge($drafts);

        foreach ($allPosts as $post) {
            $post->tags()->attach(
                Tag::inRandomOrder()->limit(random_int(1, 3))->pluck('id')
            );

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
