<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qurban_registrations', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('service_type')->default('kurban')->index();
            $table->string('animal_type');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('payment_status')->default('belum_bayar')->index();
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qurban_registrations');
    }
};
