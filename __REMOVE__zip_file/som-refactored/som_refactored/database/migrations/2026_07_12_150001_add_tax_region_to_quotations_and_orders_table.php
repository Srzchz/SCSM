<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_order_management_sales_quotations', function (Blueprint $table) {
            $table->foreignId('tax_region_id')->nullable()->after('customer_id')->constrained('tax_regions');
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('tax_region_id')->nullable()->after('customer_id')->constrained('tax_regions');
        });

        // Backfill existing rows to the default region so old data keeps
        // showing the tax region it was actually taxed at (Philippines 12%).
        $defaultId = DB::table('tax_regions')->where('is_default', true)->value('id');
        if ($defaultId) {
            DB::table('sales_order_management_sales_quotations')->whereNull('tax_region_id')->update(['tax_region_id' => $defaultId]);
            DB::table('sales_orders')->whereNull('tax_region_id')->update(['tax_region_id' => $defaultId]);
        }
    }

    public function down(): void
    {
        Schema::table('sales_order_management_sales_quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_region_id');
        });
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_region_id');
        });
    }
};
