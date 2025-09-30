<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceSetting;
use App\Models\Workspace;

class AttendanceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all workspaces
        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            // Check if attendance settings already exist for this workspace
            $existingSettings = AttendanceSetting::where('workspace_id', $workspace->id)->first();
            
            if (!$existingSettings) {
                // Create default attendance settings for each workspace
                AttendanceSetting::create([
                    'workspace_id' => $workspace->id,
                    'work_start_time' => '09:00:00',
                    'work_end_time' => '17:00:00',
                    'break_start_time' => '12:00:00',
                    'break_end_time' => '13:00:00',
                    'break_duration_minutes' => 60,
                    'max_break_duration_minutes' => 120,
                    'late_tolerance_minutes' => 15,
                    'early_departure_tolerance_minutes' => 15,
                    'require_location' => false,
                    'require_photo' => false,
                    'auto_checkout' => false,
                    'auto_checkout_hours' => 8,
                    'weekend_tracking' => false,
                    'holiday_tracking' => false,
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'holidays' => [],
                    'overtime_threshold_hours' => 8.00,
                    'overtime_approval_required' => true,
                    'break_approval_required' => false,
                    'attendance_approval_required' => false,
                    'location_radius_meters' => 100,
                    'timezone' => 'UTC',
                    'is_active' => true
                ]);
            }
        }

        $this->command->info('Default attendance settings created for all workspaces!');
    }
}