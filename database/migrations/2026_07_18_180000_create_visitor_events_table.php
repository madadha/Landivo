<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('landing_page_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 30)->default('page_view')->index();
            $table->string('path')->nullable();
            $table->string('session_hash', 64)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'created_at']);
            $table->index(['landing_page_id', 'event_type']);
            $table->index(['product_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
    }
};
