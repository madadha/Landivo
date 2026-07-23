<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('status');
            $table->index(['account_id', 'status', 'sort_order'], 'products_homepage_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_homepage_sort_index');
            $table->dropColumn('sort_order');
        });
    }
};
