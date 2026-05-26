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
            $table->string('page_title', 500)->nullable()->after('bridge_delay_seconds');
            $table->text('thumbnail_url')->nullable()->after('page_title');
        });
    }

    public function down(): void
    {
        if (config('engagyo.use_shared_database')) {
            return;
        }

        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn(['page_title', 'thumbnail_url']);
        });
    }
};
