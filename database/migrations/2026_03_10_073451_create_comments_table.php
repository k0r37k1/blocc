<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->string('nickname');
            $table->string('email')->nullable();
            $table->text('content');
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_author')->default(false);
            $table->string('edit_token', 64)->nullable();
            $table->timestamps();

            $table->index(['post_id', 'is_approved', 'created_at']);
            $table->index('edit_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
