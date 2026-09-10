<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->index();
            $table->string('type')->index();
            $table->foreignId('finance_category_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('reference_no')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
