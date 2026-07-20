<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_popups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('internal_name');
            $table->string('template', 30)->default('split_offer');
            $table->string('eyebrow_ar')->nullable();
            $table->string('eyebrow_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('button_text_ar')->nullable();
            $table->string('button_text_en')->nullable();
            $table->text('button_url')->nullable();
            $table->boolean('open_new_tab')->default(false);
            $table->string('desktop_image', 512)->nullable();
            $table->string('mobile_image', 512)->nullable();
            $table->string('page_scope', 30)->default('all');
            $table->json('target_paths')->nullable();
            $table->string('locale', 10)->default('all');
            $table->string('device', 15)->default('all');
            $table->string('trigger_type', 20)->default('delay');
            $table->unsignedSmallInteger('delay_seconds')->default(2);
            $table->unsignedTinyInteger('scroll_percentage')->default(40);
            $table->string('frequency', 20)->default('once_day');
            $table->unsignedSmallInteger('priority')->default(50);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('background_color', 20)->default('#FFFFFF');
            $table->string('text_color', 20)->default('#172033');
            $table->string('button_color', 20)->default('#8A9B22');
            $table->string('button_text_color', 20)->default('#FFFFFF');
            $table->string('overlay_color', 20)->default('#0F172A');
            $table->unsignedSmallInteger('border_radius')->default(28);
            $table->unsignedSmallInteger('max_width')->default(820);
            $table->boolean('allow_close')->default(true);
            $table->boolean('close_on_backdrop')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamps();

            $table->index(['account_id', 'is_active', 'priority']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_popups');
    }
};
