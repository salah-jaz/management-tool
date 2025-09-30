@extends('layout')
@section('title')
    Working Hours Management
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">Working Hours Management</h3>
                        <p class="text-muted mb-0">Set individual working hours for each employee</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#setDefaultModal">
                            <i class="bx bx-plus me-1"></i>Set Default for All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Employee Working Hours</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Working Days</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Break Time</th>
                                        <th>Late Tolerance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        @php
                                            $workingHours = $user->workingHours;
                                            $workingDays = $workingHours ? $workingHours->getWorkingDays() : [];
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $user->avatar ?? asset('photos/default-avatar.png') }}" 
                                                         class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                                    <div>
                                                        <div class="fw-medium">{{ $user->name }}</div>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($workingHours && count($workingDays) > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($workingDays as $day)
                                                            <span class="badge bg-primary">{{ $day }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not Set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($workingHours && $workingHours->monday_start)
                                                    {{ $workingHours->monday_start->format('H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($workingHours && $workingHours->monday_end)
                                                    {{ $workingHours->monday_end->format('H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($workingHours && $workingHours->monday_break_start && $workingHours->monday_break_end)
                                                    {{ $workingHours->monday_break_start->format('H:i') }} - {{ $workingHours->monday_break_end->format('H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($workingHours)
                                                    {{ $workingHours->late_tolerance_minutes }} min
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($workingHours)
                                                    <span class="badge bg-success">Configured</span>
                                                @else
                                                    <span class="badge bg-warning">Not Set</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 align-items-center action-buttons">
                                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('attendance.working-hours.create', $user->id) }}" title="{{ $workingHours ? 'Edit Working Hours' : 'Set Working Hours' }}">
                                                        <i class="bx bx-time"></i>
                                                    </a>
                                                    @if($workingHours)
                                                    <a class="btn btn-sm btn-outline-info" href="{{ route('attendance.working-hours.show', $user->id) }}" title="View Details">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyFromUser({{ $user->id }})" title="Copy to Others">
                                                        <i class="bx bx-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bx bx-user display-4"></i>
                                                    <p class="mt-2">No employees found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Default Modal -->
    <div class="modal fade" id="setDefaultModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Set Default Working Hours for All Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('attendance.working-hours.set-default') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" class="form-control" value="09:00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" class="form-control" value="17:00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Break Start Time</label>
                                <input type="time" name="break_start" class="form-control" value="12:00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Break End Time</label>
                                <input type="time" name="break_end" class="form-control" value="13:00">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Working Days <span class="text-danger">*</span></label>
                                <div class="row">
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="working_days[]" value="{{ $day }}" 
                                                       id="day_{{ $day }}" {{ in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="day_{{ $day }}">
                                                    {{ ucfirst($day) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Late Tolerance (minutes) <span class="text-danger">*</span></label>
                                <input type="number" name="late_tolerance_minutes" class="form-control" value="15" min="0" max="120" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Overtime Threshold (hours) <span class="text-danger">*</span></label>
                                <input type="number" name="overtime_threshold_hours" class="form-control" value="8" min="1" max="24" required>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="flexible_hours" id="flexible_hours">
                                    <label class="form-check-label" for="flexible_hours">
                                        Allow flexible working hours
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="weekend_work" id="weekend_work">
                                    <label class="form-check-label" for="weekend_work">
                                        Allow weekend work
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Set Default for All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyFromUser(userId) {
            // Implementation for copying working hours from one user to others
            alert('Copy functionality will be implemented');
        }
    </script>
    @endpush
    @endauthBoth
@endsection
