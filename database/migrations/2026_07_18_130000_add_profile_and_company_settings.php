<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('avatar_url')->nullable()->after('email');
        });

        Schema::table('accounts', function (Blueprint $table): void {
            $table->string('logo_path')->nullable()->after('description');
            $table->text('company_details')->nullable()->after('logo_path');
            $table->string('default_locale', 10)->default('ar')->after('company_details');
            $table->string('phone_country_code', 8)->default('971')->after('default_locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('avatar_url');
        });

        Schema::table('accounts', function (Blueprint $table): void {
            $table->dropColumn(['logo_path', 'company_details', 'default_locale', 'phone_country_code']);
        });
    }
};
