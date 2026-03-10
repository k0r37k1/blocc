<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'parent_id' => null,
            'nickname' => fake()->firstName(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'content' => fake()->paragraph(),
            'ip_address' => fake()->ipv4(),
            'is_approved' => true,
            'is_author' => false,
            'edit_token' => Str::random(64),
        ];
    }

    /**
     * Mark the comment as pending (not approved).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => false,
        ]);
    }

    /**
     * Mark the comment as an author reply.
     */
    public function asAuthor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_author' => true,
            'is_approved' => true,
        ]);
    }

    /**
     * Make it a reply to a given parent comment.
     */
    public function replyTo(int $parentId): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parentId,
        ]);
    }
}
