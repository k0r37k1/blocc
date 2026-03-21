<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->boolean('is_system')->default(false)->after('sort_order');
        });

        $now = now();
        foreach ([
            ['title' => 'Blog', 'slug' => '__blog', 'sort_order' => 0],
            ['title' => 'Archive', 'slug' => '__archive', 'sort_order' => 100],
        ] as $item) {
            if (! DB::table('pages')->where('slug', $item['slug'])->exists()) {
                DB::table('pages')->insert([
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'body' => '',
                    'body_raw' => '',
                    'status' => 'published',
                    'published_at' => $now,
                    'sort_order' => $item['sort_order'],
                    'show_in_nav' => true,
                    'show_in_footer' => false,
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('pages')->whereIn('slug', ['__blog', '__archive'])->delete();

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('is_system');
        });
    }
};
