<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table): void {
            $table->id();
            $table->morphs('rsvpable');
            $table->string('name');
            $table->string('phone');
            $table->unsignedSmallInteger('person_count')->default(1);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
