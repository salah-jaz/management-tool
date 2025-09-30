<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AttendancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create attendance permissions
        $permissions = [
            'create_attendance',
            'manage_attendance',
            'edit_attendance',
            'delete_attendance',
            'approve_attendance',
            'view_attendance_reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Assign basic permissions to member role
        $memberRole = Role::where('name', 'member')->first();
        if ($memberRole) {
            $memberRole->givePermissionTo(['manage_attendance', 'view_attendance_reports']);
        }

        $this->command->info('Attendance permissions created and assigned successfully!');
    }
}
