<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->timestamps();
        });

        Schema::create('responsible_parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('contractor_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('postcode')->nullable();
            $table->string('taman')->nullable();
            $table->string('area_type')->nullable();
            $table->string('socioeconomic_group')->nullable();
            $table->foreignId('responsible_party_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('disposal_centres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->json('accepted_category_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('source')->default('citizen');
            $table->string('status')->default('new')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('photos')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->string('reporter_email')->nullable();
            $table->foreignId('waste_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('risk_score')->default(50)->index();
            $table->text('false_report_reason')->nullable();
            $table->unsignedSmallInteger('suggested_manpower')->nullable();
            $table->unsignedSmallInteger('suggested_lorries')->nullable();
            $table->timestamp('suggested_deadline')->nullable();
            $table->foreignId('suggested_disposal_centre_id')->nullable()->constrained('disposal_centres')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsible_party_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('deadline')->nullable();
            $table->unsignedSmallInteger('manpower')->nullable();
            $table->unsignedSmallInteger('lorries')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('resolution_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cctv_detections', function (Blueprint $table) {
            $table->id();
            $table->string('video_path');
            $table->foreignId('waste_category_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('activity_detected')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->json('raw_result')->nullable();
            $table->foreignId('report_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('cctv_detections');
        Schema::dropIfExists('resolution_proofs');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('disposal_centres');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('responsible_parties');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('contractors');
        Schema::dropIfExists('waste_categories');
    }
};
