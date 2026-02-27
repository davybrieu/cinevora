<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('torrentio_realdebrid_key')->nullable()->after('remember_token');
            $table->json('torrentio_providers')->nullable()->after('torrentio_realdebrid_key');
            $table->json('torrentio_language')->nullable()->after('torrentio_providers');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'torrentio_realdebrid_key',
                'torrentio_providers',
                'torrentio_language',
            ]);
        });
    }
};
