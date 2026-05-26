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

        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('short_code', 20)->unique();
            $table->text('original_url');
            $table->string('redirect_mode', 20)->default('bridge');
            $table->unsignedTinyInteger('bridge_delay_seconds')->default(5);
            $table->string('page_title', 500)->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();

            $table->index('short_code');
        });
    }

    public function down(): void
    {
        if (config('engagyo.use_shared_database')) {
            return;
        }

        Schema::dropIfExists('short_links');
    }
};
