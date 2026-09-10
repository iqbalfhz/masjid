<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_schedules', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->time('imsak')->nullable();
            $table->time('fajr');
            $table->time('sunrise')->nullable();
            $table->time('dhuhr');
            $table->time('asr');
            $table->time('maghrib');
            $table->time('isha');
            $table->boolean('is_override')->default(false);
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_schedules');
    }
};
