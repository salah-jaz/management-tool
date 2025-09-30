<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->time('work_start_time')->default('09:00:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->time('break_start_time')->default('12:00:00');
            $table->time('break_end_time')->default('13:00:00');
            $table->integer('break_duration_minutes')->default(60);
            $table->integer('max_break_duration_minutes')->default(120);
            $table->integer('late_tolerance_minutes')->default(15);
            $table->integer('early_departure_tolerance_minutes')->default(15);
            $table->boolean('require_location')->default(false);
            $table->boolean('require_photo')->default(false);
            $table->boolean('auto_checkout')->default(false);
            $table->integer('auto_checkout_hours')->default(8);
            $table->boolean('weekend_tracking')->default(false);
            $table->boolean('holiday_tracking')->default(false);
            $table->json('working_days')->default('["monday", "tuesday", "wednesday", "thursday", "friday"]');
            $table->json('holidays')->nullable();
            $table->decimal('overtime_threshold_hours', 5, 2)->default(8.00);
            $table->boolean('overtime_approval_required')->default(true);
            $table->boolean('break_approval_required')->default(false);
            $table->boolean('attendance_approval_required')->default(false);
            $table->text('location_radius_meters')->default(100);
            $table->text('timezone')->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            
            // Indexes
            $table->unique('workspace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};