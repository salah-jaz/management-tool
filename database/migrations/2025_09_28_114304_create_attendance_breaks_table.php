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
        Schema::create('attendance_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('break_type', ['lunch', 'coffee', 'personal', 'meeting', 'other'])->default('other');
            $table->time('break_start_time');
            $table->time('break_end_time')->nullable();
            $table->time('break_duration')->nullable();
            $table->text('break_reason')->nullable();
            $table->enum('break_status', ['active', 'completed', 'cancelled'])->default('active');
            $table->boolean('is_approved')->default(true);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['attendance_id', 'break_status']);
            $table->index(['user_id', 'break_start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');
    }
};
