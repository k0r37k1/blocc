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
            ['name' => 'Allgemein', 'slug' => 'allgemein', 'description' => 'Allgemeine Beiträge.', 'color' => '#16a34a'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
