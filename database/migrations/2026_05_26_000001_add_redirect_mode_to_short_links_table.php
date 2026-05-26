<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('engagyo.use_shared_database')) {
            return;
        }

        Schema::table('short_links', function (Blueprint $table) {
            $table->string('redirect_mode', 20)->default('bridge')->after('original_url');
            $table->unsignedTinyInteger('bridge_delay_seconds')->default(5)->after('redirect_mode');
        });
    }

    public function down(): void
    {
        if (config('engagyo.use_shared_database')) {
            return;
        }

        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn(['redirect_mode', 'bridge_delay_seconds']);
        });
    }
};
