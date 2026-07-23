<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->string('entity', 30);
            $table->string('status', 20)->default('queued');
            $table->string('source_path')->nullable();
            $table->string('result_path')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('succeeded_rows')->default(0);
            $table->unsignedBigInteger('failed_rows')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'status', 'created_at']);
            $table->index(['account_id', 'entity', 'type']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->index(['account_id', 'name'], 'customers_account_name_index');
            $table->index(['account_id', 'email'], 'customers_account_email_index');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['account_id', 'created_at'], 'orders_account_created_index');
            $table->index(['account_id', 'source'], 'orders_account_source_index');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->index(['account_id', 'status'], 'products_account_status_index');
        });

        Schema::table('product_translations', function (Blueprint $table): void {
            $table->index(['locale', 'name'], 'product_translations_locale_name_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_translations', fn (Blueprint $table) => $table->dropIndex('product_translations_locale_name_index'));
        Schema::table('products', fn (Blueprint $table) => $table->dropIndex('products_account_status_index'));
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_account_created_index');
            $table->dropIndex('orders_account_source_index');
        });
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropIndex('customers_account_name_index');
            $table->dropIndex('customers_account_email_index');
        });
        Schema::dropIfExists('data_transfers');
    }
};
