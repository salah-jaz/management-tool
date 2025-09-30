<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixAttendanceHours extends Command
{
    protected $signature = 'attendance:fix-hours';
    protected $description = 'Fix attendance hours for existing records';

    public function handle()
    {
        $this->info('Fixing attendance hours...');
        
        $attendances = Attendance::all();
        
        foreach ($attendances as $attendance) {
            $this->line("Processing ID: {$attendance->id}");
            
            if (!$attendance->check_in_time) {
                $this->warn("  No check-in time, skipping");
                continue;
            }
            
            $checkIn = Carbon::parse($attendance->check_in_time);
            
            if ($attendance->check_out_time) {
                $checkOut = Carbon::parse($attendance->check_out_time);
            } else {
                // If no check-out, assume 8 hours of work
                $checkOut = $checkIn->copy()->addHours(8);
            }
            
            // Calculate work hours
            $totalMinutes = $checkOut->diffInMinutes($checkIn);
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            
            $workHours = sprintf('%02d:%02d:00', $hours, $minutes);
            
            $attendance->update([
                'total_work_hours' => $workHours,
                'total_break_hours' => '00:00:00',
                'net_work_hours' => $workHours,
            ]);
            
            $this->info("  Updated: {$workHours}");
        }
        
        $this->info('Done!');
        return Command::SUCCESS;
    }
}

