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
        Schema::create('user_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            
            // Working hours for each day of the week
            $table->time('monday_start')->nullable();
            $table->time('monday_end')->nullable();
            $table->time('monday_break_start')->nullable();
            $table->time('monday_break_end')->nullable();
            $table->boolean('monday_working')->default(true);
            
            $table->time('tuesday_start')->nullable();
            $table->time('tuesday_end')->nullable();
            $table->time('tuesday_break_start')->nullable();
            $table->time('tuesday_break_end')->nullable();
            $table->boolean('tuesday_working')->default(true);
            
            $table->time('wednesday_start')->nullable();
            $table->time('wednesday_end')->nullable();
            $table->time('wednesday_break_start')->nullable();
            $table->time('wednesday_break_end')->nullable();
            $table->boolean('wednesday_working')->default(true);
            
            $table->time('thursday_start')->nullable();
            $table->time('thursday_end')->nullable();
            $table->time('thursday_break_start')->nullable();
            $table->time('thursday_break_end')->nullable();
            $table->boolean('thursday_working')->default(true);
            
            $table->time('friday_start')->nullable();
            $table->time('friday_end')->nullable();
            $table->time('friday_break_start')->nullable();
            $table->time('friday_break_end')->nullable();
            $table->boolean('friday_working')->default(true);
            
            $table->time('saturday_start')->nullable();
            $table->time('saturday_end')->nullable();
            $table->time('saturday_break_start')->nullable();
            $table->time('saturday_break_end')->nullable();
            $table->boolean('saturday_working')->default(false);
            
            $table->time('sunday_start')->nullable();
            $table->time('sunday_end')->nullable();
            $table->time('sunday_break_start')->nullable();
            $table->time('sunday_break_end')->nullable();
            $table->boolean('sunday_working')->default(false);
            
            // General settings
            $table->integer('late_tolerance_minutes')->default(15); // Minutes after start time to consider late
            $table->integer('overtime_threshold_hours')->default(8); // Hours after which overtime starts
            $table->boolean('flexible_hours')->default(false); // Allow flexible start/end times
            $table->boolean('weekend_work')->default(false); // Allow weekend work
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'workspace_id']);
            $table->unique(['user_id', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_working_hours');
    }
};