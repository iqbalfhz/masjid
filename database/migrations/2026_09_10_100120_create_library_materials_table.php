<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_materials', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->foreignId('study_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ustadz_name')->nullable();
            $table->date('material_date')->nullable()->index();
            $table->text('description')->nullable();
            $table->unsignedInteger('downloads')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_materials');
    }
};
