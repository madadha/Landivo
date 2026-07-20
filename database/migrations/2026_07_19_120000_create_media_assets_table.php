<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk', 40)->default('public');
            // Keep the compound unique index below MySQL's 3072-byte limit
            // when using utf8mb4 (account_id + disk + path).
            $table->string('path', 512);
            $table->string('original_name')->nullable();
            $table->string('title')->nullable();
            $table->text('alt_text')->nullable();
            $table->string('folder')->nullable()->index();
            $table->string('mime_type')->nullable()->index();
            $table->string('extension', 20)->nullable()->index();
            $table->string('category', 30)->default('other')->index();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->unsignedInteger('usage_count')->default(0)->index();
            $table->json('usage_locations')->nullable();
            $table->boolean('file_exists')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'disk', 'path'], 'media_assets_account_disk_path_unique');
            $table->index(['account_id', 'category', 'usage_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
