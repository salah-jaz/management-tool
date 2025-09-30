@extends('layout')
@section('title')
    <?= get_label('attendance_details', 'Attendance Details') ?>
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card modern-attendance-header">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-1">
                                    <i class="bx bx-show me-2"></i>
                                    <?= get_label('attendance_details', 'Attendance Details') ?>
                                </h4>
                                <p class="text-muted mb-0">View detailed attendance information</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-light">
                                    <i class="bx bx-edit me-1"></i>
                                    <?= get_label('edit', 'Edit') ?>
                                </a>
                                <a href="{{ route('attendance.index') }}" class="btn btn-outline-light">
                                    <i class="bx bx-arrow-back me-1"></i>
                                    <?= get_label('back', 'Back') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Details -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-user me-2"></i>
                            <?= get_label('employee_info', 'Employee Information') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <img src="{{ $attendance->user->avatar ?? asset('photos/default-avatar.png') }}" 
                                         class="rounded-circle mb-3" width="100" height="100" alt="Avatar">
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h5>{{ $attendance->user->name }}</h5>
                                <p class="text-muted mb-2">{{ $attendance->user->email }}</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Employee ID:</small>
                                        <div class="fw-bold">{{ $attendance->user->id }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Date:</small>
                                        <div class="fw-bold">{{ $attendance->attendance_date->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-check-circle me-2"></i>
                            <?= get_label('status', 'Status') ?>
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="status-indicator mb-3">
                            <div class="status-circle bg-{{ $attendance->status_badge }} mb-2">
                                <i class="bx bx-{{ $attendance->status === 'present' ? 'check' : ($attendance->status === 'absent' ? 'x' : 'time') }}"></i>
                            </div>
                            <h5 class="mb-0">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</h5>
                            @if($attendance->late_minutes > 0)
                                <small class="text-warning">{{ $attendance->late_minutes }} minutes late</small>
                            @endif
                        </div>
                        @if($attendance->is_approved)
                            <span class="badge bg-success">
                                <i class="bx bx-check me-1"></i>Approved
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bx bx-time me-1"></i>Pending Approval
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-time me-2"></i>
                            <?= get_label('time_details', 'Time Details') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="time-card">
                                    <div class="time-icon bg-success">
                                        <i class="bx bx-log-in"></i>
                                    </div>
                                    <div class="time-content">
                                        <h6>Check In</h6>
                                        <div class="time-value">
                                            @if($attendance->check_in_time)
                                                {{ $attendance->check_in_time_formatted }}
                                            @else
                                                <span class="text-muted">Not checked in</span>
                                            @endif
                                        </div>
                                        @if($attendance->check_in_location)
                                            <small class="text-muted">{{ $attendance->check_in_location }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="time-card">
                                    <div class="time-icon bg-info">
                                        <i class="bx bx-log-out"></i>
                                    </div>
                                    <div class="time-content">
                                        <h6>Check Out</h6>
                                        <div class="time-value">
                                            @if($attendance->check_out_time)
                                                {{ $attendance->check_out_time_formatted }}
                                            @else
                                                <span class="text-muted">Not checked out</span>
                                            @endif
                                        </div>
                                        @if($attendance->check_out_location)
                                            <small class="text-muted">{{ $attendance->check_out_location }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-clock me-2"></i>
                            <?= get_label('work_hours', 'Work Hours') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="hours-card">
                                    <div class="hours-icon bg-primary">
                                        <i class="bx bx-time-five"></i>
                                    </div>
                                    <div class="hours-content">
                                        <h6>Total Work</h6>
                                        <div class="hours-value">{{ $attendance->total_work_hours_formatted }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hours-card">
                                    <div class="hours-icon bg-warning">
                                        <i class="bx bx-coffee"></i>
                                    </div>
                                    <div class="hours-content">
                                        <h6>Break Time</h6>
                                        <div class="hours-value">{{ $attendance->total_break_hours_formatted }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hours-card">
                                    <div class="hours-icon bg-success">
                                        <i class="bx bx-trending-up"></i>
                                    </div>
                                    <div class="hours-content">
                                        <h6>Net Work</h6>
                                        <div class="hours-value">{{ $attendance->net_work_hours_formatted }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hours-card">
                                    <div class="hours-icon bg-danger">
                                        <i class="bx bx-plus"></i>
                                    </div>
                                    <div class="hours-content">
                                        <h6>Overtime</h6>
                                        <div class="hours-value">
                                            @if($attendance->overtime_hours > 0)
                                                +{{ $attendance->overtime_hours }}h
                                            @else
                                                0h
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breaks Details -->
        @if($attendance->breaks->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-coffee me-2"></i>
                            <?= get_label('breaks', 'Breaks') ?> ({{ $attendance->breaks->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendance->breaks as $break)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $break->type_badge }}">
                                                    {{ ucfirst($break->break_type) }}
                                                </span>
                                            </td>
                                            <td>{{ $break->break_start_time_formatted }}</td>
                                            <td>{{ $break->break_end_time_formatted ?? '-' }}</td>
                                            <td>{{ $break->break_duration_formatted }}</td>
                                            <td>{{ $break->break_reason ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $break->status_badge }}">
                                                    {{ ucfirst($break->break_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Additional Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <?= get_label('additional_info', 'Additional Information') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Location Information</h6>
                                @if($attendance->check_in_latitude && $attendance->check_in_longitude)
                                    <p class="mb-1">
                                        <strong>Check In GPS:</strong> 
                                        {{ $attendance->check_in_latitude }}, {{ $attendance->check_in_longitude }}
                                    </p>
                                @endif
                                @if($attendance->check_out_latitude && $attendance->check_out_longitude)
                                    <p class="mb-1">
                                        <strong>Check Out GPS:</strong> 
                                        {{ $attendance->check_out_latitude }}, {{ $attendance->check_out_longitude }}
                                    </p>
                                @endif
                                @if($attendance->notes)
                                    <h6 class="mt-3">Notes</h6>
                                    <p class="text-muted">{{ $attendance->notes }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6>Approval Information</h6>
                                @if($attendance->is_approved && $attendance->approvedBy)
                                    <p class="mb-1">
                                        <strong>Approved By:</strong> {{ $attendance->approvedBy->name }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Approved At:</strong> {{ $attendance->approved_at->format('M d, Y H:i') }}
                                    </p>
                                    @if($attendance->approval_notes)
                                        <p class="mb-1">
                                            <strong>Approval Notes:</strong> {{ $attendance->approval_notes }}
                                        </p>
                                    @endif
                                @else
                                    <p class="text-muted">This attendance record is pending approval.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .modern-attendance-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .modern-attendance-header .card-title {
            color: white;
        }

        .modern-attendance-header .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .status-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto;
        }

        .time-card, .hours-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            height: 100%;
        }

        .time-icon, .hours-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .time-content, .hours-content {
            flex: 1;
        }

        .time-content h6, .hours-content h6 {
            margin-bottom: 0.25rem;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .time-value, .hours-value {
            font-size: 1.25rem;
            font-weight: bold;
            color: #495057;
        }

        .card {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .badge {
            font-size: 0.75rem;
        }
    </style>
    @endpush
    @endauthBoth
@endsection


