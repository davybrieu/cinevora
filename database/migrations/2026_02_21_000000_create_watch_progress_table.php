<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('watch_progress');
        Schema::create('watch_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('item_id');
            $table->string('item_type', 20); // movie, tv, anime
            $table->unsignedInteger('progress')->default(0); // 0-100 percentage
            $table->unsignedInteger('duration')->default(0); // total duration in seconds
            $table->unsignedSmallInteger('season')->default(0); // 0 for movie
            $table->unsignedSmallInteger('episode')->default(0); // 0 for movie
            $table->timestamps();

            $table->unique(['profile_id', 'item_id', 'item_type', 'season', 'episode'], 'watch_progress_profile_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_progress');
    }
};
