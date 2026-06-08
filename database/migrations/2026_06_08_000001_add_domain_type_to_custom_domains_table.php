<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->string('domain_type', 20)->default('subdomain')->after('domain');
            $table->string('base_domain', 253)->nullable()->after('domain_type');
            $table->string('subdomain_prefix', 63)->nullable()->after('base_domain');
        });

        DB::table('custom_domains')->orderBy('id')->each(function (object $row): void {
            $parts = explode('.', $row->domain);
            $isApex = count($parts) <= 2;

            DB::table('custom_domains')->where('id', $row->id)->update([
                'domain_type' => $isApex ? 'apex' : 'subdomain',
                'base_domain' => $isApex ? $row->domain : implode('.', array_slice($parts, 1)),
                'subdomain_prefix' => $isApex ? null : $parts[0],
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->dropColumn(['domain_type', 'base_domain', 'subdomain_prefix']);
        });
    }
};
