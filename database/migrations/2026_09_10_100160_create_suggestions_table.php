<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->string('name')->nullable();
            $table->string('contact')->nullable();
            $table->string('category')->index();
            $table->text('message');
            $table->string('status')->default('baru')->index();
            $table->text('response_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
