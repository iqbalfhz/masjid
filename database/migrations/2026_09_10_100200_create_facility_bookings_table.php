<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('purpose');
            $table->date('booking_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('menunggu')->index();
            $table->text('approval_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_bookings');
    }
};
