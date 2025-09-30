<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserWorkingHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workspace_id',
        'monday_start', 'monday_end', 'monday_break_start', 'monday_break_end', 'monday_working',
        'tuesday_start', 'tuesday_end', 'tuesday_break_start', 'tuesday_break_end', 'tuesday_working',
        'wednesday_start', 'wednesday_end', 'wednesday_break_start', 'wednesday_break_end', 'wednesday_working',
        'thursday_start', 'thursday_end', 'thursday_break_start', 'thursday_break_end', 'thursday_working',
        'friday_start', 'friday_end', 'friday_break_start', 'friday_break_end', 'friday_working',
        'saturday_start', 'saturday_end', 'saturday_break_start', 'saturday_break_end', 'saturday_working',
        'sunday_start', 'sunday_end', 'sunday_break_start', 'sunday_break_end', 'sunday_working',
        'late_tolerance_minutes',
        'overtime_threshold_hours',
        'flexible_hours',
        'weekend_work'
    ];

    protected $casts = [
        'monday_start' => 'datetime:H:i',
        'monday_end' => 'datetime:H:i',
        'monday_break_start' => 'datetime:H:i',
        'monday_break_end' => 'datetime:H:i',
        'monday_working' => 'boolean',
        'tuesday_start' => 'datetime:H:i',
        'tuesday_end' => 'datetime:H:i',
        'tuesday_break_start' => 'datetime:H:i',
        'tuesday_break_end' => 'datetime:H:i',
        'tuesday_working' => 'boolean',
        'wednesday_start' => 'datetime:H:i',
        'wednesday_end' => 'datetime:H:i',
        'wednesday_break_start' => 'datetime:H:i',
        'wednesday_break_end' => 'datetime:H:i',
        'wednesday_working' => 'boolean',
        'thursday_start' => 'datetime:H:i',
        'thursday_end' => 'datetime:H:i',
        'thursday_break_start' => 'datetime:H:i',
        'thursday_break_end' => 'datetime:H:i',
        'thursday_working' => 'boolean',
        'friday_start' => 'datetime:H:i',
        'friday_end' => 'datetime:H:i',
        'friday_break_start' => 'datetime:H:i',
        'friday_break_end' => 'datetime:H:i',
        'friday_working' => 'boolean',
        'saturday_start' => 'datetime:H:i',
        'saturday_end' => 'datetime:H:i',
        'saturday_break_start' => 'datetime:H:i',
        'saturday_break_end' => 'datetime:H:i',
        'saturday_working' => 'boolean',
        'sunday_start' => 'datetime:H:i',
        'sunday_end' => 'datetime:H:i',
        'sunday_break_start' => 'datetime:H:i',
        'sunday_break_end' => 'datetime:H:i',
        'sunday_working' => 'boolean',
        'flexible_hours' => 'boolean',
        'weekend_work' => 'boolean'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    // Scopes
    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function getWorkingHoursForDay($dayOfWeek)
    {
        $day = strtolower($dayOfWeek);
        
        return [
            'start' => $this->{$day . '_start'},
            'end' => $this->{$day . '_end'},
            'break_start' => $this->{$day . '_break_start'},
            'break_end' => $this->{$day . '_break_end'},
            'working' => $this->{$day . '_working'}
        ];
    }

    public function getExpectedStartTime($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $dayOfWeek = strtolower($date->format('l'));
        
        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        
        if (!$workingHours['working']) {
            return null;
        }
        
        return $workingHours['start'];
    }

    public function getExpectedEndTime($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $dayOfWeek = strtolower($date->format('l'));
        
        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        
        if (!$workingHours['working']) {
            return null;
        }
        
        return $workingHours['end'];
    }

    public function getExpectedBreakTime($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $dayOfWeek = strtolower($date->format('l'));
        
        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        
        if (!$workingHours['working'] || !$workingHours['break_start'] || !$workingHours['break_end']) {
            return 0;
        }
        
        $breakStart = Carbon::parse($workingHours['break_start']);
        $breakEnd = Carbon::parse($workingHours['break_end']);
        
        return $breakStart->diffInMinutes($breakEnd);
    }

    public function getExpectedWorkHours($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $dayOfWeek = strtolower($date->format('l'));
        
        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        
        if (!$workingHours['working'] || !$workingHours['start'] || !$workingHours['end']) {
            return 0;
        }
        
        $start = Carbon::parse($workingHours['start']);
        $end = Carbon::parse($workingHours['end']);
        $breakMinutes = $this->getExpectedBreakTime($date);
        
        return $start->diffInMinutes($end) - $breakMinutes;
    }

    public function isLate($checkInTime, $date = null)
    {
        $expectedStart = $this->getExpectedStartTime($date);
        
        if (!$expectedStart) {
            return false;
        }
        
        $checkIn = Carbon::parse($checkInTime);
        $expected = Carbon::parse($expectedStart);
        $tolerance = $this->late_tolerance_minutes;
        
        return $checkIn->gt($expected->addMinutes($tolerance));
    }

    public function getLateMinutes($checkInTime, $date = null)
    {
        if (!$this->isLate($checkInTime, $date)) {
            return 0;
        }
        
        $expectedStart = $this->getExpectedStartTime($date);
        $checkIn = Carbon::parse($checkInTime);
        $expected = Carbon::parse($expectedStart);
        
        return $checkIn->diffInMinutes($expected);
    }

    public function isWorkingDay($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $dayOfWeek = strtolower($date->format('l'));
        
        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        
        return $workingHours['working'];
    }

    public function getWorkingDays()
    {
        $days = [];
        $dayNames = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($dayNames as $day) {
            if ($this->{$day . '_working'}) {
                $days[] = ucfirst($day);
            }
        }
        
        return $days;
    }
}