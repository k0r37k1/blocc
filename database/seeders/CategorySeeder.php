<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed sample blog categories.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'PHP', 'slug' => 'php', 'description' => 'Articles about PHP language features and best practices.', 'color' => '#777BB4'],
            ['name' => 'Laravel', 'slug' => 'laravel', 'description' => 'Laravel framework tips, tutorials, and deep dives.', 'color' => '#FF2D20'],
            ['name' => 'DevOps', 'slug' => 'devops', 'description' => 'Deployment, CI/CD, and infrastructure topics.', 'color' => '#326CE5'],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'description' => 'JavaScript and frontend development articles.', 'color' => '#F7DF1E'],
            ['name' => 'Tutorials', 'slug' => 'tutorials', 'description' => 'Step-by-step guides and walkthroughs.', 'color' => '#16a34a'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
