<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pothole_cases', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('road_name');
            $table->string('area');
            $table->string('corridor_type');
            $table->string('status')->default('new')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('street_risk')->default(50);
            $table->unsignedTinyInteger('ai_risk_score')->default(50);
            $table->unsignedTinyInteger('traffic_impact')->default(50);
            $table->unsignedTinyInteger('safety_risk')->default(50);
            $table->unsignedTinyInteger('cost_to_fix')->default(50);
            $table->unsignedTinyInteger('rakyat_impact')->default(50);
            $table->unsignedTinyInteger('gdp_impact')->default(50);
            $table->string('dominant_impact')->default('balanced');
            $table->string('impact_reason')->nullable();
            $table->string('spend_recommendation')->nullable();
            $table->unsignedInteger('estimated_cost_rm')->default(0);
            $table->unsignedInteger('budget_spent_rm')->default(0);
            $table->string('severity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pothole_cases');
    }
};
