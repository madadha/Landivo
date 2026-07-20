<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_statuses', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('is_active');
            $table->index(['account_id', 'archived_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('notes');
            $table->index(['account_id', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['account_id', 'archived_at']);
            $table->dropColumn('archived_at');
        });

        Schema::table('order_statuses', function (Blueprint $table): void {
            $table->dropIndex(['account_id', 'archived_at']);
            $table->dropColumn('archived_at');
        });
    }
};
