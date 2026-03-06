<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('social_mastodon');
            $table->string('social_linkedin')->nullable()->after('social_twitter');
            $table->string('social_instagram')->nullable()->after('social_linkedin');
            $table->string('social_bluesky')->nullable()->after('social_instagram');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['social_linkedin', 'social_instagram', 'social_bluesky']);
            $table->string('social_mastodon')->nullable()->after('social_twitter');
        });
    }
};
