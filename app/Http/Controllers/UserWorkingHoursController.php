<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserWorkingHours;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserWorkingHoursController extends Controller
{
    protected $workspace;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('has_workspace');
        // Ensure workspace is resolved after middleware runs
        $this->middleware(function ($request, $next) {
            $this->workspace = Workspace::find(getWorkspaceId());
            return $next($request);
        });
    }

    /**
     * Display a listing of user working hours.
     */
    public function index()
    {
        $users = $this->workspace->users()->with('workingHours')->get();
        
        return view('attendance.working-hours.index', compact('users'));
    }

    /**
     * Show the form for creating/editing working hours for a user.
     */
    public function create($userId)
    {
        $user = User::findOrFail($userId);
        $workingHours = $user->workingHours ?? new UserWorkingHours();
        
        return view('attendance.working-hours.create', compact('user', 'workingHours'));
    }

    /**
     * Store or update working hours for a user.
     */
    public function store(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $request->validate([
            'monday_start' => 'nullable|date_format:H:i',
            'monday_end' => 'nullable|date_format:H:i|after:monday_start',
            'monday_break_start' => 'nullable|date_format:H:i',
            'monday_break_end' => 'nullable|date_format:H:i|after:monday_break_start',
            'monday_working' => 'boolean',
            
            'tuesday_start' => 'nullable|date_format:H:i',
            'tuesday_end' => 'nullable|date_format:H:i|after:tuesday_start',
            'tuesday_break_start' => 'nullable|date_format:H:i',
            'tuesday_break_end' => 'nullable|date_format:H:i|after:tuesday_break_start',
            'tuesday_working' => 'boolean',
            
            'wednesday_start' => 'nullable|date_format:H:i',
            'wednesday_end' => 'nullable|date_format:H:i|after:wednesday_start',
            'wednesday_break_start' => 'nullable|date_format:H:i',
            'wednesday_break_end' => 'nullable|date_format:H:i|after:wednesday_break_start',
            'wednesday_working' => 'boolean',
            
            'thursday_start' => 'nullable|date_format:H:i',
            'thursday_end' => 'nullable|date_format:H:i|after:thursday_start',
            'thursday_break_start' => 'nullable|date_format:H:i',
            'thursday_break_end' => 'nullable|date_format:H:i|after:thursday_break_start',
            'thursday_working' => 'boolean',
            
            'friday_start' => 'nullable|date_format:H:i',
            'friday_end' => 'nullable|date_format:H:i|after:friday_start',
            'friday_break_start' => 'nullable|date_format:H:i',
            'friday_break_end' => 'nullable|date_format:H:i|after:friday_break_start',
            'friday_working' => 'boolean',
            
            'saturday_start' => 'nullable|date_format:H:i',
            'saturday_end' => 'nullable|date_format:H:i|after:saturday_start',
            'saturday_break_start' => 'nullable|date_format:H:i',
            'saturday_break_end' => 'nullable|date_format:H:i|after:saturday_break_start',
            'saturday_working' => 'boolean',
            
            'sunday_start' => 'nullable|date_format:H:i',
            'sunday_end' => 'nullable|date_format:H:i|after:sunday_start',
            'sunday_break_start' => 'nullable|date_format:H:i',
            'sunday_break_end' => 'nullable|date_format:H:i|after:sunday_break_start',
            'sunday_working' => 'boolean',
            
            'late_tolerance_minutes' => 'required|integer|min:0|max:120',
            'overtime_threshold_hours' => 'required|integer|min:1|max:24',
            'flexible_hours' => 'boolean',
            'weekend_work' => 'boolean'
        ]);

        $data = $request->all();
        $data['user_id'] = $userId;
        $data['workspace_id'] = $this->workspace->id;

        $workingHours = UserWorkingHours::updateOrCreate(
            ['user_id' => $userId, 'workspace_id' => $this->workspace->id],
            $data
        );

        return redirect()->route('attendance.working-hours.index')
            ->with('success', 'Working hours updated successfully for ' . $user->name);
    }

    /**
     * Display working hours for a specific user.
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);
        $workingHours = $user->workingHours;
        
        if (!$workingHours) {
            return redirect()->route('attendance.working-hours.create', $userId)
                ->with('info', 'No working hours set for this user. Please set them up.');
        }
        
        return view('attendance.working-hours.show', compact('user', 'workingHours'));
    }

    /**
     * Get working hours for a specific user and date.
     */
    public function getWorkingHoursForDate($userId, $date = null)
    {
        $user = User::findOrFail($userId);
        $workingHours = $user->workingHours;
        
        if (!$workingHours) {
            return response()->json([
                'success' => false,
                'message' => 'No working hours set for this user'
            ]);
        }
        
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $dayOfWeek = strtolower($date->format('l'));
        
        $dayHours = $workingHours->getWorkingHoursForDay($dayOfWeek);
        
        return response()->json([
            'success' => true,
            'data' => [
                'day' => $dayOfWeek,
                'working' => $dayHours['working'],
                'start_time' => $dayHours['start'] ? $dayHours['start']->format('H:i') : null,
                'end_time' => $dayHours['end'] ? $dayHours['end']->format('H:i') : null,
                'break_start' => $dayHours['break_start'] ? $dayHours['break_start']->format('H:i') : null,
                'break_end' => $dayHours['break_end'] ? $dayHours['break_end']->format('H:i') : null,
                'expected_work_hours' => $workingHours->getExpectedWorkHours($date),
                'late_tolerance_minutes' => $workingHours->late_tolerance_minutes,
                'overtime_threshold_hours' => $workingHours->overtime_threshold_hours
            ]
        ]);
    }

    /**
     * Copy working hours from one user to another.
     */
    public function copyFromUser(Request $request, $userId)
    {
        $request->validate([
            'source_user_id' => 'required|exists:users,id'
        ]);
        
        $user = User::findOrFail($userId);
        $sourceUser = User::findOrFail($request->source_user_id);
        $sourceWorkingHours = $sourceUser->workingHours;
        
        if (!$sourceWorkingHours) {
            return redirect()->back()->with('error', 'Source user has no working hours set.');
        }
        
        $data = $sourceWorkingHours->toArray();
        unset($data['id'], $data['user_id'], $data['created_at'], $data['updated_at']);
        $data['user_id'] = $userId;
        $data['workspace_id'] = $this->workspace->id;
        
        UserWorkingHours::updateOrCreate(
            ['user_id' => $userId, 'workspace_id' => $this->workspace->id],
            $data
        );
        
        return redirect()->back()->with('success', 'Working hours copied successfully from ' . $sourceUser->name);
    }

    /**
     * Set default working hours for all users in workspace.
     */
    public function setDefaultForAll(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'working_days' => 'required|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'late_tolerance_minutes' => 'required|integer|min:0|max:120',
            'overtime_threshold_hours' => 'required|integer|min:1|max:24'
        ]);
        
        $users = $this->workspace->users;
        $workingDays = $request->working_days;
        
        foreach ($users as $user) {
            $data = [
                'user_id' => $user->id,
                'workspace_id' => $this->workspace->id,
                'late_tolerance_minutes' => $request->late_tolerance_minutes,
                'overtime_threshold_hours' => $request->overtime_threshold_hours,
                'flexible_hours' => $request->boolean('flexible_hours'),
                'weekend_work' => $request->boolean('weekend_work')
            ];
            
            // Set working hours for each day
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            foreach ($days as $day) {
                $data[$day . '_working'] = in_array($day, $workingDays);
                if (in_array($day, $workingDays)) {
                    $data[$day . '_start'] = $request->start_time;
                    $data[$day . '_end'] = $request->end_time;
                    $data[$day . '_break_start'] = $request->break_start;
                    $data[$day . '_break_end'] = $request->break_end;
                }
            }
            
            UserWorkingHours::updateOrCreate(
                ['user_id' => $user->id, 'workspace_id' => $this->workspace->id],
                $data
            );
        }
        
        return redirect()->back()->with('success', 'Default working hours set for all users.');
    }
}