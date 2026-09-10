<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosque_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('Masjid An-Nur');
            $table->string('tagline')->nullable();
            $table->string('address')->default('Lantai P3a, Tangcity Mall');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->text('history')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('qris_image')->nullable();
            $table->string('logo')->nullable();
            $table->string('maps_embed_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('prayer_calculation_method')->nullable();
            $table->json('prayer_reminder_settings')->nullable();
            $table->json('social_links')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mosque_settings');
    }
};
