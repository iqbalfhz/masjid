<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('poster_image')->nullable();
            $table->date('event_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->default('Masjid An-Nur, Lantai P3a Tangcity Mall');
            $table->string('category')->nullable();
            $table->boolean('rsvp_enabled')->default(false);
            $table->unsignedInteger('rsvp_quota')->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('approval_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
