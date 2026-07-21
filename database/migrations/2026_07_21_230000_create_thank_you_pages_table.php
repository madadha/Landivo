<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thank_you_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('internal_name');
            $table->string('slug');
            $table->boolean('is_active')->default(true)->index();
            $table->string('default_locale', 5)->default('ar');
            $table->string('template')->default('premium');
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('message_ar')->nullable();
            $table->text('message_en')->nullable();
            $table->string('button_text_ar')->nullable();
            $table->string('button_text_en')->nullable();
            $table->string('redirect_url')->nullable();
            $table->unsignedInteger('countdown_seconds')->default(0);
            $table->string('image_ar')->nullable();
            $table->string('image_en')->nullable();
            $table->string('font_family')->default('cairo');
            $table->string('alignment', 10)->default('center');
            $table->string('background_color', 20)->default('#F6F7FB');
            $table->string('card_color', 20)->default('#FFFFFF');
            $table->string('title_color', 20)->default('#172033');
            $table->string('text_color', 20)->default('#667085');
            $table->string('button_color', 20)->default('#172033');
            $table->string('button_text_color', 20)->default('#FFFFFF');
            $table->unsignedSmallInteger('border_radius')->default(28);
            $table->longText('head_code')->nullable();
            $table->longText('body_code')->nullable();
            $table->longText('custom_css')->nullable();
            $table->json('tracking_keys')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thank_you_pages');
    }
};
