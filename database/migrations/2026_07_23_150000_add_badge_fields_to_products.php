<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('badge_is_active')->default(false)->after('sort_order');
            $table->string('badge_text_ar', 80)->nullable()->after('badge_is_active');
            $table->string('badge_text_en', 80)->nullable()->after('badge_text_ar');
            $table->string('badge_style', 20)->default('pill')->after('badge_text_en');
            $table->string('badge_background_color', 20)->default('#d97706')->after('badge_style');
            $table->string('badge_text_color', 20)->default('#ffffff')->after('badge_background_color');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'badge_is_active',
                'badge_text_ar',
                'badge_text_en',
                'badge_style',
                'badge_background_color',
                'badge_text_color',
            ]);
        });
    }
};
