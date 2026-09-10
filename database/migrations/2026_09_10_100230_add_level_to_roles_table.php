<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('permission.table_names.roles'), function (Blueprint $table): void {
            $table->unsignedTinyInteger('level')->default(9)->after('guard_name');
            $table->string('label')->nullable()->after('level');
        });
    }

    public function down(): void
    {
        Schema::table(config('permission.table_names.roles'), function (Blueprint $table): void {
            $table->dropColumn(['level', 'label']);
        });
    }
};
