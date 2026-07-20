<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('options')->nullable()->after('metadata');
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->json('option_values')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'sku']);
        });

        Schema::table('landing_pages', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            $table->boolean('track_inventory')->default(false)->after('settings')->index();
            $table->unsignedInteger('stock_quantity')->default(0)->after('track_inventory');
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('stock_quantity');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
        });

        Schema::table('order_statuses', function (Blueprint $table): void {
            $table->boolean('deduct_inventory')->default(false)->after('is_final')->index();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('inventory_deducted_at')->nullable()->after('follow_up_completed_at')->index();
        });

        DB::table('order_statuses')
            ->whereIn('slug', ['delivered', 'completed', 'fulfilled'])
            ->orWhere('name_ar', 'like', '%تم التسليم%')
            ->update(['deduct_inventory' => true]);
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('inventory_deducted_at'));
        Schema::table('order_statuses', fn (Blueprint $table) => $table->dropColumn('deduct_inventory'));
        Schema::table('order_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('product_variant_id'));
        Schema::table('landing_pages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn(['track_inventory', 'stock_quantity', 'low_stock_threshold']);
        });
        Schema::dropIfExists('product_variants');
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('options'));
    }
};
