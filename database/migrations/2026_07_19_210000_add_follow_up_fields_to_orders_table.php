<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('follow_up_at')->nullable()->after('notes')->index();
            $table->text('follow_up_note')->nullable()->after('follow_up_at');
            $table->timestamp('follow_up_completed_at')->nullable()->after('follow_up_note')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['follow_up_at']);
            $table->dropIndex(['follow_up_completed_at']);
            $table->dropColumn(['follow_up_at', 'follow_up_note', 'follow_up_completed_at']);
        });
    }
};
