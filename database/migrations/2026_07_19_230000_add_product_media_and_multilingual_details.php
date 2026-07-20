<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_translations', function (Blueprint $table): void {
            $table->longText('details')->nullable()->after('description');
        });

        Schema::create('product_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->nullable()->index();
            $table->string('media_type', 20)->default('image')->index();
            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['product_id', 'locale', 'sort_order']);
        });

        Schema::create('product_variant_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['product_variant_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_translations');
        Schema::dropIfExists('product_media');
        Schema::table('product_translations', fn (Blueprint $table) => $table->dropColumn('details'));
    }
};
