<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->foreignId('custom_domain_id')
                ->nullable()
                ->after('user_id')
                ->constrained('custom_domains')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_domain_id');
        });
    }
};
