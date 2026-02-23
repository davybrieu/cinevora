<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('wishlists', 'watchlists');
    }

    public function down(): void
    {
        Schema::rename('watchlists', 'wishlists');
    }
};
