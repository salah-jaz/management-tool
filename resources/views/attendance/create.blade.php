@extends('layout')
@section('title')
    <?= get_label('add_attendance', 'Add Attendance') ?>
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
                                    <i class="bx bx-plus me-2"></i>
                                    <?= get_label('add_attendance', 'Add Attendance') ?>
                                </h4>
                                <p class="text-muted mb-0">Create a new attendance record manually</p>
                            </div>
                            <div>
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
                        <form action="{{ route('attendance.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('user', 'User') ?> <span class="text-danger">*</span></label>
                                    <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                        <option value=""><?= get_label('select_user', 'Select User') ?></option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('date', 'Date') ?> <span class="text-danger">*</span></label>
                                    <input type="date" name="attendance_date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                           value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                                    @error('attendance_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_in_time', 'Check In Time') ?> <span class="text-danger">*</span></label>
                                    <input type="time" name="check_in_time" class="form-control @error('check_in_time') is-invalid @enderror" 
                                           value="{{ old('check_in_time') }}" required>
                                    @error('check_in_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_out_time', 'Check Out Time') ?></label>
                                    <input type="time" name="check_out_time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                           value="{{ old('check_out_time') }}">
                                    @error('check_out_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('status', 'Status') ?> <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                        <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Late</option>
                                        <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                        <option value="leave" {{ old('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_in_location', 'Check In Location') ?></label>
                                    <input type="text" name="check_in_location" class="form-control @error('check_in_location') is-invalid @enderror" 
                                           value="{{ old('check_in_location') }}" placeholder="e.g., Office, Remote">
                                    @error('check_in_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('check_out_location', 'Check Out Location') ?></label>
                                    <input type="text" name="check_out_location" class="form-control @error('check_out_location') is-invalid @enderror" 
                                           value="{{ old('check_out_location') }}" placeholder="e.g., Office, Remote">
                                    @error('check_out_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('latitude', 'Latitude') ?></label>
                                    <input type="number" name="check_in_latitude" class="form-control @error('check_in_latitude') is-invalid @enderror" 
                                           value="{{ old('check_in_latitude') }}" step="any" placeholder="e.g., 40.7128">
                                    @error('check_in_latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= get_label('longitude', 'Longitude') ?></label>
                                    <input type="number" name="check_in_longitude" class="form-control @error('check_in_longitude') is-invalid @enderror" 
                                           value="{{ old('check_in_longitude') }}" step="any" placeholder="e.g., -74.0060">
                                    @error('check_in_longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label"><?= get_label('notes', 'Notes') ?></label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                                              placeholder="Additional notes about this attendance record...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i>
                                    <?= get_label('cancel', 'Cancel') ?>
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>
                                    <?= get_label('save', 'Save') ?>
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
                            <?= get_label('help', 'Help') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bx bx-info-circle me-2"></i>Quick Tips:</h6>
                            <ul class="mb-0">
                                <li>Select the user for whom you're creating the attendance record</li>
                                <li>Choose the appropriate date and times</li>
                                <li>Select the correct status (Present, Absent, Late, etc.)</li>
                                <li>Add location details if available</li>
                                <li>Include any relevant notes</li>
                            </ul>
                        </div>
                        @if($settings)
                        <div class="mt-3">
                            <h6>Current Settings:</h6>
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

        .alert ul {
            padding-left: 1.5rem;
        }

        .alert ul li {
            margin-bottom: 0.25rem;
        }
    </style>
    @endpush
    @endauthBoth
@endsection






