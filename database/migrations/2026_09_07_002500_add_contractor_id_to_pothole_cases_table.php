<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pothole_cases', function (Blueprint $table) {
            $table->foreignId('contractor_id')
                ->nullable()
                ->after('photo_path')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pothole_cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contractor_id');
        });
    }
};
