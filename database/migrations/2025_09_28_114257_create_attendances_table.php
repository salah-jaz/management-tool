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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('user_id');
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->time('total_work_hours')->nullable();
            $table->time('total_break_hours')->nullable();
            $table->time('net_work_hours')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave'])->default('absent');
            $table->enum('check_in_type', ['manual', 'automatic', 'mobile'])->default('manual');
            $table->enum('check_out_type', ['manual', 'automatic', 'mobile'])->default('manual');
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('late_minutes', 5, 2)->default(0);
            $table->decimal('early_departure_minutes', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('check_in_location')->nullable();
            $table->text('check_out_location')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['workspace_id', 'user_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
            $table->unique(['workspace_id', 'user_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};