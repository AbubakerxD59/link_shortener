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

        if (Schema::hasColumn('short_links', 'source')) {
            return;
        }

        Schema::table('short_links', function (Blueprint $table) {
            $table->string('source', 64)->nullable()->after('thumbnail_url')->index();
        });
    }

    public function down(): void
    {
        if (config('engagyo.use_shared_database')) {
            return;
        }

        if (! Schema::hasColumn('short_links', 'source')) {
            return;
        }

        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
