<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->foreignId('order_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('source', 30)->default('admin')->after('photo_path');
            $table->boolean('is_verified_purchase')->default(false)->after('source');
            $table->string('customer_email')->nullable()->after('name');
            $table->string('customer_phone', 40)->nullable()->after('customer_email');

            $table->unique('order_id');
            $table->index(['landing_page_id', 'is_approved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropUnique(['order_id']);
            $table->dropIndex(['landing_page_id', 'is_approved', 'created_at']);
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn(['source', 'is_verified_purchase', 'customer_email', 'customer_phone']);
        });
    }
};
