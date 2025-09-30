@extends('layout')
@section('title')
    <?= get_label('edit_attendance', 'Edit Attendance') ?>
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
                                    <i class="bx bx-edit me-2"></i>
                                    <?= get_label('edit_attendance', 'Edit Attendance') ?>
                                </h4>
                                <p class="text-muted mb-0">Update attendance record for {{ $attendance->user->name }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('attendance.show', $attendance) }}" class="btn btn-outline-light">
                                    <i class="bx bx-show me-1"></i>
                                    <?= get_label('view', 'View') ?>
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

        <!-- Form Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-edit me-2"></i>
                            <?= get_label('attendance_details', 'Attendance Details') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('attendance.update', $attendance) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('user', 'User') ?></label>
                                    <div class="form-control-plaintext">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $attendance->user->avatar ?? asset('photos/default-avatar.png') }}" 
                                                 class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                            <div>
                                                <div class="fw-bold">{{ $attendance->user->name }}</div>
                                                <small class="text-muted">{{ $attendance->user->email }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('date', 'Date') ?></label>
                                    <div class="form-control-plaintext">
                                        <div class="fw-bold">{{ $attendance->attendance_date->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_in_time', 'Check In Time') ?></label>
                                    <input type="time" name="check_in_time" class="form-control @error('check_in_time') is-invalid @enderror" 
                                           value="{{ old('check_in_time', $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '') }}">
                                    @error('check_in_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_out_time', 'Check Out Time') ?></label>
                                    <input type="time" name="check_out_time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                           value="{{ old('check_out_time', $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '') }}">
                                    @error('check_out_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('status', 'Status') ?> <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Absent</option>
                                        <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Late</option>
                                        <option value="half_day" {{ old('status', $attendance->status) == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                        <option value="leave" {{ old('status', $attendance->status) == 'leave' ? 'selected' : '' }}>Leave</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_in_location', 'Check In Location') ?></label>
                                    <input type="text" name="check_in_location" class="form-control @error('check_in_location') is-invalid @enderror" 
                                           value="{{ old('check_in_location', $attendance->check_in_location) }}" placeholder="e.g., Office, Remote">
                                    @error('check_in_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_out_location', 'Check Out Location') ?></label>
                                    <input type="text" name="check_out_location" class="form-control @error('check_out_location') is-invalid @enderror" 
                                           value="{{ old('check_out_location', $attendance->check_out_location) }}" placeholder="e.g., Office, Remote">
                                    @error('check_out_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('latitude', 'Latitude') ?></label>
                                    <input type="number" name="check_in_latitude" class="form-control @error('check_in_latitude') is-invalid @enderror" 
                                           value="{{ old('check_in_latitude', $attendance->check_in_latitude) }}" step="any" placeholder="e.g., 40.7128">
                                    @error('check_in_latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('longitude', 'Longitude') ?></label>
                                    <input type="number" name="check_in_longitude" class="form-control @error('check_in_longitude') is-invalid @enderror" 
                                           value="{{ old('check_in_longitude', $attendance->check_in_longitude) }}" step="any" placeholder="e.g., -74.0060">
                                    @error('check_in_longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label"><?= get_label('notes', 'Notes') ?></label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                                              placeholder="Additional notes about this attendance record...">{{ old('notes', $attendance->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('attendance.show', $attendance) }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i>
                                    <?= get_label('cancel', 'Cancel') ?>
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>
                                    <?= get_label('update', 'Update') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <?= get_label('current_info', 'Current Information') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="info-section mb-3">
                            <h6>Current Status</h6>
                            <span class="badge bg-{{ $attendance->status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            </span>
                        </div>
                        
                        <div class="info-section mb-3">
                            <h6>Time Information</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">Check In:</small>
                                    <div class="fw-bold">
                                        {{ $attendance->check_in_time_formatted ?? 'Not set' }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Check Out:</small>
                                    <div class="fw-bold">
                                        {{ $attendance->check_out_time_formatted ?? 'Not set' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-section mb-3">
                            <h6>Work Hours</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">Total Work:</small>
                                    <div class="fw-bold">{{ $attendance->total_work_hours_formatted }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Break Time:</small>
                                    <div class="fw-bold">{{ $attendance->total_break_hours_formatted }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Net Work:</small>
                                    <div class="fw-bold">{{ $attendance->net_work_hours_formatted }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Overtime:</small>
                                    <div class="fw-bold">
                                        @if($attendance->overtime_hours > 0)
                                            +{{ $attendance->overtime_hours }}h
                                        @else
                                            0h
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($attendance->breaks->count() > 0)
                        <div class="info-section">
                            <h6>Breaks ({{ $attendance->breaks->count() }})</h6>
                            @foreach($attendance->breaks as $break)
                                <div class="break-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ $break->type_badge }}">{{ ucfirst($break->break_type) }}</span>
                                        <small class="text-muted">{{ $break->break_duration_formatted }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif

                        @if($settings)
                        <div class="mt-3">
                            <h6>Settings Reference</h6>
                            <small class="text-muted">
                                <div>Work Start: {{ $settings->getWorkStartTimeFormatted() }}</div>
                                <div>Work End: {{ $settings->getWorkEndTimeFormatted() }}</div>
                                <div>Late Tolerance: {{ $settings->late_tolerance_minutes }} minutes</div>
                            </small>
                        </div>
                        @endif
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

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .card {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .info-section {
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .info-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-section h6 {
            color: #495057;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .break-item {
            padding: 0.25rem 0;
        }

        .break-item:not(:last-child) {
            border-bottom: 1px solid #f8f9fa;
        }

        .badge {
            font-size: 0.75rem;
        }
    </style>
    @endpush
    @endauthBoth
@endsection


