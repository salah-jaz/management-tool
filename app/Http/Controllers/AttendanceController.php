<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceSetting;
use App\Models\User;
use App\Models\UserWorkingHours;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    protected $workspace;
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request)
    {
        $query = Attendance::forWorkspace($this->workspace->id)
            ->with(['user', 'breaks'])
            ->orderBy('attendance_date', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        } elseif ($request->filled('date')) {
            $query->forDate($request->date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            if ($request->approval_status === 'pending') {
                $query->pendingApproval();
            } elseif ($request->approval_status === 'approved') {
                $query->approved();
            }
        }

        $attendances = $query->paginate(20);
        $users = $this->workspace->users;
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();

        // Calculate total hours (fallback to computed minutes when stored total is null/zero)
        $totalHours = $attendances->sum(function($attendance) {
            if ($attendance->total_work_hours) {
                $seconds = max(0, strtotime($attendance->total_work_hours) - strtotime('00:00:00'));
                if ($seconds > 0) {
                    return $seconds / 3600;
                }
            }

            if ($attendance->check_in_time && $attendance->check_out_time) {
                $date = $attendance->attendance_date instanceof \Carbon\Carbon
                    ? $attendance->attendance_date->format('Y-m-d')
                    : (string) $attendance->attendance_date;

                $inStr = $attendance->check_in_time instanceof \Carbon\Carbon
                    ? $attendance->check_in_time->format('H:i:s')
                    : (string) $attendance->check_in_time;

                $outStr = $attendance->check_out_time instanceof \Carbon\Carbon
                    ? $attendance->check_out_time->format('H:i:s')
                    : (string) $attendance->check_out_time;

                $in = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$inStr);
                $out = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$outStr);
                if ($out->lt($in)) {
                    $out->addDay();
                }
                $totalMinutes = max(0, (int) $in->diffInMinutes($out));

                // Sum break durations if relation is loaded
                if ($attendance->relationLoaded('breaks')) {
                    $breakSeconds = $attendance->breaks
                        ->where('break_status', 'completed')
                        ->sum(function ($break) {
                            $duration = (string) ($break->break_duration ?? '00:00:00');
                            return max(0, strtotime($duration) - strtotime('00:00:00'));
                        });
                } else {
                    $breakSeconds = (int) $attendance->breaks()
                        ->where('break_status', 'completed')
                        ->sum(\Illuminate\Support\Facades\DB::raw('TIME_TO_SEC(break_duration)'));
                }

                $workMinutes = max(0, $totalMinutes - (int) round($breakSeconds / 60));
                return $workMinutes / 60;
            }

            return 0;
        });

        return view('attendance.index', compact('attendances', 'users', 'settings', 'totalHours'));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create()
    {
        $users = $this->workspace->users;
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
        
        return view('attendance.create', compact('users', 'settings'));
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,absent,late,half_day,leave',
            'notes' => 'nullable|string|max:1000',
            'check_in_location' => 'nullable|string|max:255',
            'check_out_location' => 'nullable|string|max:255',
            'check_in_latitude' => 'nullable|numeric|between:-90,90',
            'check_in_longitude' => 'nullable|numeric|between:-180,180',
            'check_out_latitude' => 'nullable|numeric|between:-90,90',
            'check_out_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if attendance already exists for this user and date
        $existingAttendance = Attendance::forWorkspace($this->workspace->id)
            ->forUser($request->user_id)
            ->forDate($request->attendance_date)
            ->first();

        if ($existingAttendance) {
            return redirect()->back()
                ->with('error', 'Attendance record already exists for this user and date.')
                ->withInput();
        }

        $attendance = Attendance::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $request->user_id,
            'attendance_date' => $request->attendance_date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'status' => $request->status,
            'check_in_type' => 'manual',
            'check_out_type' => $request->check_out_time ? 'manual' : null,
            'notes' => $request->notes,
            'check_in_location' => $request->check_in_location,
            'check_out_location' => $request->check_out_location,
            'check_in_latitude' => $request->check_in_latitude,
            'check_in_longitude' => $request->check_in_longitude,
            'check_out_latitude' => $request->check_out_latitude,
            'check_out_longitude' => $request->check_out_longitude,
            'is_approved' => !$this->requiresApproval(),
        ]);

        // Calculate work hours if both check-in and check-out times are provided
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $this->calculateAttendanceHours($attendance);
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'breaks', 'approvedBy']);
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
        
        return view('attendance.show', compact('attendance', 'settings'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        $users = $this->workspace->users;
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
        
        return view('attendance.edit', compact('attendance', 'users', 'settings'));
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,absent,late,half_day,leave',
            'notes' => 'nullable|string|max:1000',
            'check_in_location' => 'nullable|string|max:255',
            'check_out_location' => 'nullable|string|max:255',
            'check_in_latitude' => 'nullable|numeric|between:-90,90',
            'check_in_longitude' => 'nullable|numeric|between:-180,180',
            'check_out_latitude' => 'nullable|numeric|between:-90,90',
            'check_out_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $attendance->update([
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'status' => $request->status,
            'notes' => $request->notes,
            'check_in_location' => $request->check_in_location,
            'check_out_location' => $request->check_out_location,
            'check_in_latitude' => $request->check_in_latitude,
            'check_in_longitude' => $request->check_in_longitude,
            'check_out_latitude' => $request->check_out_latitude,
            'check_out_longitude' => $request->check_out_longitude,
            'is_approved' => false, // Reset approval when updated
        ]);

        // Recalculate work hours
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $this->calculateAttendanceHours($attendance);
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Attendance $attendance)
    {
        try {
            \Log::info('Attempting to delete attendance', [
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'workspace_id' => $attendance->workspace_id
            ]);
            
            $attendance->delete();
            
            \Log::info('Attendance deleted successfully', [
                'attendance_id' => $attendance->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting attendance', [
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check in for attendance.
     */
    public function checkIn(Request $request)
    {
        if (!$this->workspace || !$this->user) {
            return response()->json([
                'success' => false,
                'message' => 'Workspace or user not resolved. Please reselect a workspace and try again.'
            ], 422);
        }
        $today = Carbon::today();
        
        // Check if already checked in today
        $existingAttendance = Attendance::forWorkspace($this->workspace->id)
            ->forUser($this->user->id)
            ->forDate($today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today.'
            ]);
        }

        // Get user's working hours
        $workingHours = $this->user->workingHours;
        $checkInTime = now();
        $status = 'present';

        // Check if it's a working day and if user is late (only if working hours are configured)
        if ($workingHours) {
            if (!$workingHours->isWorkingDay($today)) {
                $status = 'weekend'; // Mark as weekend work if working hours don't include this day
            } elseif ($workingHours->isLate($checkInTime->format('H:i:s'), $today)) {
                $status = 'late';
            }
        } else {
            // Fallback to global settings if no user-specific working hours
            $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
            if ($settings) {
                $expectedStart = Carbon::parse($settings->work_start_time);
                $tolerance = $settings->late_tolerance_minutes;
                
                if ($checkInTime->gt($expectedStart->addMinutes($tolerance))) {
                    $status = 'late';
                }
            }
        }

        $attendanceData = [
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
            'attendance_date' => $today,
            'check_in_time' => $checkInTime->format('H:i:s'),
            'check_in_type' => $request->type ?? 'manual',
            'status' => $status,
            'is_approved' => !$this->requiresApproval(),
        ];

        // Add location data if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $attendanceData['check_in_latitude'] = $request->latitude;
            $attendanceData['check_in_longitude'] = $request->longitude;
            $attendanceData['check_in_location'] = $request->location ?? 'GPS Location';
        }

        if ($existingAttendance) {
            $existingAttendance->update($attendanceData);
            $attendance = $existingAttendance;
        } else {
            $attendance = Attendance::create($attendanceData);
        }

        // Calculate late minutes using user-specific working hours
        $this->calculateLateMinutes($attendance);

        // Reload attendance to get fresh data
        $attendance = $attendance->fresh(['breaks']);

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully.',
            'attendance' => $attendance,
            'status' => $status,
            'check_in_time' => $attendance->check_in_time,
            'check_in_time_formatted' => $attendance->check_in_time_formatted
        ]);
    }

    /**
     * Check out for attendance.
     */
    public function checkOut(Request $request)
    {
        if (!$this->workspace || !$this->user) {
            return response()->json([
                'success' => false,
                'message' => 'Workspace or user not resolved. Please reselect a workspace and try again.'
            ], 422);
        }
        $today = Carbon::today();
        
        $attendance = Attendance::forWorkspace($this->workspace->id)
            ->forUser($this->user->id)
            ->forDate($today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'You must check in first.'
            ]);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out today.'
            ]);
        }

        // Check for active breaks
        $activeBreak = $attendance->breaks()->active()->first();
        if ($activeBreak) {
            return response()->json([
                'success' => false,
                'message' => 'Please end your active break before checking out.'
            ]);
        }

        $attendance->update([
            'check_out_time' => now()->format('H:i:s'),
            'check_out_type' => $request->type ?? 'manual',
        ]);

        // Add location data if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $attendance->update([
                'check_out_latitude' => $request->latitude,
                'check_out_longitude' => $request->longitude,
                'check_out_location' => $request->location ?? 'GPS Location',
            ]);
        }

        // Calculate work hours
        $this->calculateAttendanceHours($attendance);

        // Reload attendance to get updated data
        $attendance = $attendance->fresh(['breaks']);

        return response()->json([
            'success' => true,
            'message' => 'Checked out successfully.',
            'attendance' => $attendance,
            'work_hours' => $attendance->total_work_hours,
            'break_hours' => $attendance->total_break_hours
        ]);
    }

    /**
     * Start a break.
     */
    public function startBreak(Request $request)
    {
        $todayString = Carbon::now(config('app.timezone'))->toDateString();
        
        $workspaceId = $this->workspace ? $this->workspace->id : null;
        $attendance = Attendance::query()
            ->when($workspaceId, function ($q) use ($workspaceId) {
                return $q->forWorkspace($workspaceId);
            })
            ->forUser($this->user->id)
            ->forDate($todayString)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'You must check in first.'
            ]);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out.'
            ]);
        }

        // Check for active breaks
        $activeBreak = $attendance->breaks()->active()->first();
        if ($activeBreak) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active break.'
            ]);
        }

        $breakData = [
            'attendance_id' => $attendance->id,
            'workspace_id' => $workspaceId,
            'user_id' => $this->user->id,
            'break_type' => $request->break_type ?? 'other',
            'break_start_time' => now(config('app.timezone'))->format('H:i:s'),
            'break_reason' => $request->break_reason,
            'break_status' => 'active',
            'is_approved' => !$this->requiresBreakApproval(),
        ];

        \Log::info('Creating break', $breakData);
        
        $break = AttendanceBreak::create($breakData);

        return response()->json([
            'success' => true,
            'message' => 'Break started successfully.',
            'break' => $break
        ]);
    }

    /**
     * End a break.
     */
    public function endBreak(Request $request)
    {
        $todayString = Carbon::now(config('app.timezone'))->toDateString();
        
        $workspaceId = $this->workspace ? $this->workspace->id : null;
        $attendance = Attendance::query()
            ->when($workspaceId, function ($q) use ($workspaceId) {
                return $q->forWorkspace($workspaceId);
            })
            ->forUser($this->user->id)
            ->forDate($todayString)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance record found.'
            ]);
        }

        $activeBreak = $attendance->breaks()->active()->first();
        if (!$activeBreak) {
            return response()->json([
                'success' => false,
                'message' => 'No active break found.'
            ]);
        }

        $activeBreak->endBreak();

        // Recalculate attendance hours
        $this->calculateAttendanceHours($attendance);

        return response()->json([
            'success' => true,
            'message' => 'Break ended successfully.',
            'attendance' => $attendance->load('breaks')
        ]);
    }

    /**
     * Get current attendance status.
     */
    public function getCurrentStatus()
    {
        $todayString = Carbon::now(config('app.timezone'))->toDateString();
        
        $workspaceId = $this->workspace ? $this->workspace->id : null;

        $query = Attendance::query()
            ->forUser($this->user->id)
            ->with('breaks');

        $attendance = (clone $query)
            ->forDate($todayString)
            ->first();
        
        // Only show today's attendance, don't fallback to previous days
        if (!$attendance) {
            \Log::info('Attendance tracker: no record found for today', [
                'user_id' => $this->user ? $this->user->id : null,
                'workspace_id' => $workspaceId,
                'today' => $todayString,
            ]);
        }

        $activeBreak = $attendance ? $attendance->breaks()->active()->first() : null;

        $attendanceData = null;
        if ($attendance) {
            $breakSeconds = $attendance->breaks->where('break_status', 'completed')->sum(function ($break) {
                $duration = (string) ($break->break_duration ?? '00:00:00');
                return max(0, strtotime($duration) - strtotime('00:00:00'));
            });
            $breakMinutes = (int) round($breakSeconds / 60);

            $attendanceData = [
                'id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : null,
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'check_in_time_formatted' => $attendance->check_in_time_formatted ?? '--:--',
                'check_out_time_formatted' => $attendance->check_out_time_formatted ?? '--:--',
                'computed_work_hours_formatted' => $attendance->computed_work_hours_formatted ?? '00:00',
                'total_work_hours_formatted' => $attendance->computed_work_hours_formatted ?? '00:00',
                'total_break_hours_formatted' => sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60),
                'breaks' => $attendance->breaks->map(function($b) {
                    return [
                        'id' => $b->id,
                        'break_type' => $b->break_type,
                        'break_status' => $b->break_status,
                        'break_start_time_formatted' => $b->break_start_time_formatted,
                        'break_end_time_formatted' => $b->break_end_time_formatted,
                        'break_duration_formatted' => $b->break_duration_formatted,
                        'status_badge' => $b->status_badge,
                        'type_badge' => $b->type_badge,
                        'break_reason' => $b->break_reason,
                    ];
                })->values(),
            ];
        }

        $status = $attendance ? $this->getAttendanceStatus($attendance, $activeBreak) : 'not_checked_in';

        return response()->json([
            'attendance' => $attendanceData,
            'active_break' => $activeBreak ? [
                'id' => $activeBreak->id,
                'break_type' => $activeBreak->break_type,
                'break_start_time_formatted' => $activeBreak->break_start_time_formatted,
            ] : null,
            'status' => $status
        ]);
    }

    /**
     * Get weekly summary for tracker.
     */
    public function getWeeklySummary()
    {
        $startOfWeek = Carbon::now(config('app.timezone'))->startOfWeek();
        $endOfWeek = Carbon::now(config('app.timezone'))->endOfWeek();

        $workspaceId = $this->workspace ? $this->workspace->id : null;
        
        $query = Attendance::query()
            ->when($workspaceId, function ($q) use ($workspaceId) {
                return $q->forWorkspace($workspaceId);
            })
            ->forUser($this->user->id)
            ->with('breaks')
            ->whereBetween('attendance_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);

        $attendances = $query->get();

        $totalWorkMinutes = 0;
        $totalBreakMinutes = 0;
        $daysPresent = 0;
        $overtimeHours = 0;

        foreach ($attendances as $attendance) {
            if ($attendance->status === 'present' || $attendance->status === 'late' || $attendance->status === 'half_day') {
                $daysPresent++;
            }

            // Work minutes
            if ($attendance->total_work_hours) {
                $sec = max(0, strtotime($attendance->total_work_hours) - strtotime('00:00:00'));
                $totalWorkMinutes += (int) round($sec / 60);
            } elseif ($attendance->check_in_time && $attendance->check_out_time) {
                $date = $attendance->attendance_date instanceof Carbon ? $attendance->attendance_date->format('Y-m-d') : (string) $attendance->attendance_date;
                $in = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.(string)$attendance->check_in_time);
                $out = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.(string)$attendance->check_out_time);
                if ($out->lt($in)) {
                    $out->addDay();
                }
                $mins = max(0, $in->diffInMinutes($out));
                $breakMins = (int) round(($attendance->breaks->where('break_status', 'completed')->sum(function ($b) {
                    $dur = (int) ($b->break_duration ?? 0);
                    return $dur * 60; // seconds
                })) / 60);
                $totalWorkMinutes += max(0, $mins - $breakMins);
            }

            // Break minutes
            if ($attendance->total_break_hours) {
                $sec = max(0, strtotime($attendance->total_break_hours) - strtotime('00:00:00'));
                $totalBreakMinutes += (int) round($sec / 60);
            } else {
                $totalBreakMinutes += (int) $attendance->breaks->where('break_status', 'completed')->sum('break_duration');
            }

            $overtimeHours += (float) ($attendance->overtime_hours ?? 0);
        }

        $summary = [
            'days_present' => $daysPresent,
            'total_work' => sprintf('%02d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60),
            'total_break' => sprintf('%02d:%02d', intdiv($totalBreakMinutes, 60), $totalBreakMinutes % 60),
            'overtime_hours' => round($overtimeHours, 2),
        ];

        return response()->json(['success' => true, 'summary' => $summary]);
    }

    /**
     * Calculate attendance hours.
     */
    private function calculateAttendanceHours(Attendance $attendance)
    {
        if (!$attendance->check_in_time || !$attendance->check_out_time) {
            \Log::info('Cannot calculate hours: missing check-in or check-out time', [
                'attendance_id' => $attendance->id,
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time
            ]);
            return;
        }

        $checkIn = Carbon::parse($attendance->check_in_time);
        $checkOut = Carbon::parse($attendance->check_out_time);
        
        // Calculate total break time (in whole minutes)
        $totalBreakMinutes = (int) round(
            $attendance->breaks()
                ->where('break_status', 'completed')
                ->sum(DB::raw('TIME_TO_SEC(break_duration)')) / 60
        );

        // Calculate work minutes and clamp to non-negative
        $totalMinutes = max(0, (int) $checkOut->diffInMinutes($checkIn));
        $workMinutes = max(0, $totalMinutes - $totalBreakMinutes);

        // Normalize hours/minutes (all non-negative)
        $workHours = intdiv($workMinutes, 60);
        $workMinutesRemainder = $workMinutes % 60;

        $breakMinutesClamped = max(0, $totalBreakMinutes);
        $breakHours = intdiv($breakMinutesClamped, 60);
        $breakMinutesRemainder = $breakMinutesClamped % 60;

        $netHours = intdiv($workMinutes, 60);
        $netMinutesRemainder = $workMinutes % 60;

        $updateData = [
            'total_work_hours' => sprintf('%02d:%02d:00', $workHours, $workMinutesRemainder),
            'total_break_hours' => sprintf('%02d:%02d:00', $breakHours, $breakMinutesRemainder),
            'net_work_hours' => sprintf('%02d:%02d:00', $netHours, $netMinutesRemainder),
            'overtime_hours' => $this->calculateOvertime($attendance, $workMinutes),
        ];

        \Log::info('Calculating attendance hours', [
            'attendance_id' => $attendance->id,
            'check_in' => $attendance->check_in_time,
            'check_out' => $attendance->check_out_time,
            'total_minutes' => $totalMinutes,
            'break_minutes' => $totalBreakMinutes,
            'work_minutes' => $workMinutes,
            'update_data' => $updateData
        ]);

        $attendance->update($updateData);
    }

    /**
     * Calculate late minutes.
     */
    private function calculateLateMinutes(Attendance $attendance)
    {
        $user = $attendance->user;
        $workingHours = $user->workingHours;
        
        if (!$workingHours) {
            // Fallback to global settings if no user-specific working hours
            $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
            if (!$settings) return;

            $checkInTime = Carbon::parse($attendance->check_in_time);
            $expectedStartTime = Carbon::parse($settings->work_start_time);
            $tolerance = $settings->late_tolerance_minutes;

            if ($checkInTime->gt($expectedStartTime->addMinutes($tolerance))) {
                $lateMinutes = $checkInTime->diffInMinutes($expectedStartTime);
                $attendance->update([
                    'late_minutes' => $lateMinutes,
                    'status' => 'late'
                ]);
            }
            return;
        }

        // Use user-specific working hours
        $lateMinutes = $workingHours->getLateMinutes($attendance->check_in_time, $attendance->attendance_date);
        
        if ($lateMinutes > 0) {
            $attendance->update([
                'late_minutes' => $lateMinutes,
                'status' => 'late'
            ]);
        }
    }

    /**
     * Calculate overtime hours.
     */
    private function calculateOvertime(Attendance $attendance, $workMinutes)
    {
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
        if (!$settings) return 0;

        $thresholdMinutes = $settings->overtime_threshold_hours * 60;
        $overtimeMinutes = max(0, $workMinutes - $thresholdMinutes);
        
        return round($overtimeMinutes / 60, 2);
    }

    /**
     * Get attendance status.
     */
    private function getAttendanceStatus($attendance, $activeBreak)
    {
        if (!$attendance) {
            return 'not_checked_in';
        }

        if (!$attendance->check_in_time) {
            return 'not_checked_in';
        }

        if ($activeBreak) {
            return 'on_break';
        }

        if (!$attendance->check_out_time) {
            return 'checked_in';
        }

        return 'checked_out';
    }

    /**
     * Check if attendance requires approval.
     */
    private function requiresApproval()
    {
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
        return $settings ? $settings->attendance_approval_required : false;
    }

    /**
     * Check if breaks require approval.
     */
    private function requiresBreakApproval()
    {
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();
        return $settings ? $settings->break_approval_required : false;
    }

    /**
     * Approve an attendance record.
     */
    public function approve(Request $request, Attendance $attendance)
    {
        $attendance->update([
            'is_approved' => true,
            'approved_by' => $this->user->id,
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance record approved successfully.'
        ]);
    }

    /**
     * Get attendance statistics.
     */
    public function getStatistics(Request $request)
    {
        $query = Attendance::forWorkspace($this->workspace->id);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        } else {
            // Default to current month
            $query->whereMonth('attendance_date', now()->month)
                  ->whereYear('attendance_date', now()->year);
        }

        $statistics = [
            'total_days' => $query->count(),
            'present_days' => $query->clone()->where('status', 'present')->count(),
            'absent_days' => $query->clone()->where('status', 'absent')->count(),
            'late_days' => $query->clone()->where('status', 'late')->count(),
            'half_days' => $query->clone()->where('status', 'half_day')->count(),
            'leave_days' => $query->clone()->where('status', 'leave')->count(),
            'total_work_hours' => $query->clone()->sum(DB::raw('TIME_TO_SEC(total_work_hours)')) / 3600,
            'total_break_hours' => $query->clone()->sum(DB::raw('TIME_TO_SEC(total_break_hours)')) / 3600,
            'total_overtime_hours' => $query->clone()->sum('overtime_hours'),
        ];

        return response()->json($statistics);
    }

    /**
     * Display attendance reports.
     */
public function reports(Request $request)
    {
        $query = Attendance::forWorkspace($this->workspace->id)
            ->with(['user', 'breaks']);

        // Filter by user
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        } else {
            // Default to current month
            $query->whereMonth('attendance_date', now()->month)
                  ->whereYear('attendance_date', now()->year);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();
        $users = $this->workspace->users;
        $settings = AttendanceSetting::forWorkspace($this->workspace->id)->first();

        // Calculate summary statistics
        $summary = $this->calculateReportSummary($attendances);

        // Group by user for detailed reports
        $userReports = $this->generateUserReports($attendances);

        // Generate charts data
        $chartsData = $this->generateChartsData($attendances);

        return view('attendance.reports', compact('attendances', 'users', 'settings', 'summary', 'userReports', 'chartsData'));
    }

    /**
     * Get user-wise attendance statistics.
     */
    public function getUserWiseStats(Request $request)
    {
        $query = Attendance::forWorkspace($this->workspace->id)
            ->with(['user']);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->forDateRange($request->start_date, $request->end_date);
        } else {
            // Default to current month
            $query->whereMonth('attendance_date', now()->month)
                  ->whereYear('attendance_date', now()->year);
        }

        $attendances = $query->get();

        $userStats = $attendances->groupBy('user_id')->map(function($userAttendances) {
            $user = $userAttendances->first()->user;
            $totalDays = $userAttendances->count();
            $presentDays = $userAttendances->where('status', 'present')->count();
            $absentDays = $userAttendances->where('status', 'absent')->count();
            $lateDays = $userAttendances->where('status', 'late')->count();
            $halfDays = $userAttendances->where('status', 'half_day')->count();
            $leaveDays = $userAttendances->where('status', 'leave')->count();
            
            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_avatar' => $user->avatar,
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'half_days' => $halfDays,
                'leave_days' => $leaveDays,
                'total_work_hours' => $userAttendances->sum('total_work_hours'),
                'total_break_hours' => $userAttendances->sum('total_break_hours'),
                'total_overtime_hours' => $userAttendances->sum('overtime_hours'),
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
                'punctuality_rate' => $totalDays > 0 ? round((($presentDays - $lateDays) / $totalDays) * 100, 1) : 0,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $userStats,
            'summary' => [
                'total_users' => $userStats->count(),
                'avg_attendance_rate' => $userStats->avg('attendance_rate'),
                'avg_punctuality_rate' => $userStats->avg('punctuality_rate'),
            ]
        ]);
    }

    /**
     * Calculate report summary statistics.
     */
    private function calculateReportSummary($attendances)
    {
        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', 'present')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $halfDays = $attendances->where('status', 'half_day')->count();
        $leaveDays = $attendances->where('status', 'leave')->count();

        $totalWorkHours = $attendances->sum(function($attendance) {
            return $attendance->total_work_hours ? 
                (strtotime($attendance->total_work_hours) - strtotime('00:00:00')) / 3600 : 0;
        });

        $totalBreakHours = $attendances->sum(function($attendance) {
            return $attendance->total_break_hours ? 
                (strtotime($attendance->total_break_hours) - strtotime('00:00:00')) / 3600 : 0;
        });

        $totalOvertimeHours = $attendances->sum('overtime_hours');

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'half_days' => $halfDays,
            'leave_days' => $leaveDays,
            'attendance_rate' => $totalDays > 0 ? round(($presentDays + $halfDays) / $totalDays * 100, 2) : 0,
            'punctuality_rate' => $totalDays > 0 ? round(($presentDays) / $totalDays * 100, 2) : 0,
            'total_work_hours' => round($totalWorkHours, 2),
            'total_break_hours' => round($totalBreakHours, 2),
            'total_overtime_hours' => round($totalOvertimeHours, 2),
            'average_work_hours' => $totalDays > 0 ? round($totalWorkHours / $totalDays, 2) : 0,
        ];
    }

    /**
     * Generate user-specific reports.
     */
    private function generateUserReports($attendances)
    {
        $userReports = [];
        $groupedByUser = $attendances->groupBy('user_id');

        foreach ($groupedByUser as $userId => $userAttendances) {
            $user = $userAttendances->first()->user;
            $summary = $this->calculateReportSummary($userAttendances);
            
            $userReports[] = [
                'user' => $user,
                'summary' => $summary,
                'attendances' => $userAttendances
            ];
        }

        return $userReports;
    }

    /**
     * Generate charts data for reports.
     */
    private function generateChartsData($attendances)
    {
        // Daily attendance chart
        $dailyData = $attendances->groupBy(function($attendance) {
            return $attendance->attendance_date->format('Y-m-d');
        })->map(function($dayAttendances) {
            return [
                'present' => $dayAttendances->where('status', 'present')->count(),
                'absent' => $dayAttendances->where('status', 'absent')->count(),
                'late' => $dayAttendances->where('status', 'late')->count(),
                'half_day' => $dayAttendances->where('status', 'half_day')->count(),
                'leave' => $dayAttendances->where('status', 'leave')->count(),
            ];
        });

        // Status distribution
        $statusDistribution = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
        ];

        // Weekly summary
        $weeklyData = $attendances->groupBy(function($attendance) {
            return $attendance->attendance_date->format('Y-W');
        })->map(function($weekAttendances) {
            return $this->calculateReportSummary($weekAttendances);
        });

        return [
            'daily' => $dailyData,
            'status_distribution' => $statusDistribution,
            'weekly' => $weeklyData,
        ];
    }
}
