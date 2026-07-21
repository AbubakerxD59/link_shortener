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
            if (! Schema::hasColumn('short_links', 'page_title')) {
                $table->string('page_title', 500)->nullable()->after('bridge_delay_seconds');
            }

            if (! Schema::hasColumn('short_links', 'thumbnail_url')) {
                $table->text('thumbnail_url')->nullable()->after('page_title');
            }
        });
    }

    public function down(): void
    {
        if (config('engagyo.use_shared_database')) {
            return;
        }

        Schema::table('short_links', function (Blueprint $table) {
            $columns = collect(['page_title', 'thumbnail_url'])
                ->filter(fn (string $column) => Schema::hasColumn('short_links', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
