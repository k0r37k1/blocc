<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $status = fake()->randomElement(PostStatus::cases());

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => collect(fake()->paragraphs(3))
                ->map(fn (string $paragraph) => "<p>{$paragraph}</p>")
                ->implode(''),
            'status' => $status,
            'published_at' => $status === PostStatus::Published ? now() : (
                $status === PostStatus::Scheduled ? fake()->dateTimeBetween('+1 day', '+1 month') : null
            ),
            'category_id' => Category::factory(),
            'excerpt' => null,
            'reading_time' => 1,
            'featured_image_alt' => null,
        ];
    }

    /**
     * Set the post as published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Published,
            'published_at' => fake()->dateTimeBetween('-3 months'),
        ]);
    }

    /**
     * Set the post as scheduled for a future publish date.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Scheduled,
            'published_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    /**
     * Set the post as draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Draft,
            'published_at' => null,
        ]);
    }
}
