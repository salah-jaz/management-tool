<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateAttendanceHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:recalculate-hours {--workspace= : Workspace ID to recalculate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate work hours for all attendance records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workspaceId = $this->option('workspace');
        
        $query = Attendance::query();
        if ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        }
        
        $attendances = $query->get();
        $this->info("Found {$attendances->count()} attendance records to process...");
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($attendances as $attendance) {
            $this->line("Processing attendance ID: {$attendance->id}");
            
            // Skip if no check-in time
            if (!$attendance->check_in_time) {
                $this->warn("  Skipping - No check-in time");
                $skipped++;
                continue;
            }
            
            // Calculate hours based on available data
            $workHours = $this->calculateWorkHours($attendance);
            $breakHours = $this->calculateBreakHours($attendance);
            
            // Update the attendance record
            $attendance->update([
                'total_work_hours' => $workHours,
                'total_break_hours' => $breakHours,
                'net_work_hours' => $workHours, // For now, net = total work hours
            ]);
            
            $this->info("  Updated - Work: {$workHours}, Break: {$breakHours}");
            $updated++;
        }
        
        $this->info("\nRecalculation complete!");
        $this->info("Updated: {$updated} records");
        $this->info("Skipped: {$skipped} records");
        
        return Command::SUCCESS;
    }
    
    private function calculateWorkHours(Attendance $attendance)
    {
        if (!$attendance->check_in_time) {
            return '00:00:00';
        }
        
        try {
            $checkIn = Carbon::parse($attendance->check_in_time);
            
            // If no check-out time, calculate from check-in to end of day
            if (!$attendance->check_out_time) {
                $checkOut = $attendance->attendance_date->copy()->setTime(18, 0, 0); // Default 6 PM end time
            } else {
                $checkOut = Carbon::parse($attendance->check_out_time);
            }
            
            // Ensure check-out is after check-in
            if ($checkOut->lte($checkIn)) {
                $this->warn("  Check-out time is before or equal to check-in time, skipping");
                return '00:00:00';
            }
            
            // Calculate total minutes
            $totalMinutes = $checkOut->diffInMinutes($checkIn);
            
            // Subtract break time
            $breakMinutes = $this->calculateBreakMinutes($attendance);
            $workMinutes = max(0, $totalMinutes - $breakMinutes);
            
            // Convert to hours:minutes:seconds format
            $hours = floor($workMinutes / 60);
            $minutes = $workMinutes % 60;
            
            return sprintf('%02d:%02d:00', $hours, $minutes);
        } catch (\Exception $e) {
            $this->error("  Error calculating hours: " . $e->getMessage());
            return '00:00:00';
        }
    }
    
    private function calculateBreakHours(Attendance $attendance)
    {
        $breakMinutes = $this->calculateBreakMinutes($attendance);
        $hours = floor($breakMinutes / 60);
        $minutes = $breakMinutes % 60;
        
        return sprintf('%02d:%02d:00', $hours, $minutes);
    }
    
    private function calculateBreakMinutes(Attendance $attendance)
    {
        return $attendance->breaks()
            ->where('break_status', 'completed')
            ->sum(DB::raw('TIME_TO_SEC(break_duration)')) / 60;
    }
}
