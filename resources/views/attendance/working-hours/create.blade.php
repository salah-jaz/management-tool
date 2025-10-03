@extends('layout')
@section('title')
    Set Working Hours - {{ $user->name }}
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">Set Working Hours</h3>
                        <p class="text-muted mb-0">Configure working hours for {{ $user->name }}</p>
                    </div>
                    <div>
                        <a href="{{ route('attendance.working-hours.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Working Hours Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Working Hours Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('attendance.working-hours.store', $user->id) }}" method="POST">
                            @csrf
                            
                            <!-- General Settings -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">General Settings</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Late Tolerance (minutes)</label>
                                    <input type="number" name="late_tolerance_minutes" class="form-control" 
                                           value="{{ $workingHours->late_tolerance_minutes ?? 15 }}" min="0" max="120" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Overtime Threshold (hours)</label>
                                    <input type="number" name="overtime_threshold_hours" class="form-control" 
                                           value="{{ $workingHours->overtime_threshold_hours ?? 8 }}" min="1" max="24" required>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="flexible_hours" id="flexible_hours" 
                                               {{ ($workingHours->flexible_hours ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="flexible_hours">
                                            Flexible Working Hours
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Daily Working Hours -->
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">Daily Working Hours</h6>
                                </div>
                            </div>

                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                @php
                                    $dayData = $workingHours ? $workingHours->getWorkingHoursForDay($day) : [];
                                    $isWorking = $dayData['working'] ?? ($day === 'saturday' || $day === 'sunday' ? false : true);
                                @endphp
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input day-checkbox" type="checkbox" name="{{ $day }}_working" 
                                                   id="{{ $day }}_working" {{ $isWorking ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium" for="{{ $day }}_working">
                                                {{ ucfirst($day) }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Start Time</label>
                                        <input type="time" name="{{ $day }}_start" class="form-control day-input" 
                                               value="{{ $dayData['start'] ? $dayData['start']->format('H:i') : '09:00' }}" 
                                               {{ !$isWorking ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">End Time</label>
                                        <input type="time" name="{{ $day }}_end" class="form-control day-input" 
                                               value="{{ $dayData['end'] ? $dayData['end']->format('H:i') : '17:00' }}" 
                                               {{ !$isWorking ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Break Start</label>
                                        <input type="time" name="{{ $day }}_break_start" class="form-control day-input" 
                                               value="{{ $dayData['break_start'] ? $dayData['break_start']->format('H:i') : '12:00' }}" 
                                               {{ !$isWorking ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Break End</label>
                                        <input type="time" name="{{ $day }}_break_end" class="form-control day-input" 
                                               value="{{ $dayData['break_end'] ? $dayData['break_end']->format('H:i') : '13:00' }}" 
                                               {{ !$isWorking ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mt-4">
                                            <span class="badge bg-info" id="{{ $day }}_hours">
                                                @if($isWorking)
                                                    {{ $workingHours ? $workingHours->getExpectedWorkHours(Carbon\Carbon::now()->startOfWeek()->addDays(array_search($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']))) : 8 }}h
                                                @else
                                                    0h
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <button type="button" class="btn btn-outline-primary" onclick="copyToAllDays()">
                                                <i class="bx bx-copy me-1"></i>Copy Monday to All Days
                                            </button>
                                        </div>
                                        <div>
                                            <a href="{{ route('attendance.working-hours.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary">Save Working Hours</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Enable/disable day inputs based on checkbox
        document.querySelectorAll('.day-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const day = this.name.replace('_working', '');
                const inputs = document.querySelectorAll(`[name^="${day}_"]:not([name$="_working"])`);
                inputs.forEach(input => {
                    input.disabled = !this.checked;
                    if (!this.checked) {
                        input.value = '';
                    }
                });
                updateDayHours(day);
            });
        });

        // Update work hours when times change
        document.querySelectorAll('.day-input').forEach(input => {
            input.addEventListener('change', function() {
                const day = this.name.split('_')[0];
                updateDayHours(day);
            });
        });

        function updateDayHours(day) {
            const checkbox = document.getElementById(`${day}_working`);
            if (!checkbox.checked) {
                document.getElementById(`${day}_hours`).textContent = '0h';
                return;
            }

            const startTime = document.querySelector(`[name="${day}_start"]`).value;
            const endTime = document.querySelector(`[name="${day}_end"]`).value;
            const breakStart = document.querySelector(`[name="${day}_break_start"]`).value;
            const breakEnd = document.querySelector(`[name="${day}_break_end"]`).value;

            if (startTime && endTime) {
                const start = new Date(`2000-01-01 ${startTime}`);
                const end = new Date(`2000-01-01 ${endTime}`);
                let workMinutes = (end - start) / (1000 * 60);

                if (breakStart && breakEnd) {
                    const breakStartTime = new Date(`2000-01-01 ${breakStart}`);
                    const breakEndTime = new Date(`2000-01-01 ${breakEnd}`);
                    const breakMinutes = (breakEndTime - breakStartTime) / (1000 * 60);
                    workMinutes -= breakMinutes;
                }

                const hours = Math.floor(workMinutes / 60);
                const minutes = workMinutes % 60;
                document.getElementById(`${day}_hours`).textContent = `${hours}h ${Math.round(minutes)}m`;
            }
        }

        function copyToAllDays() {
            const mondayStart = document.querySelector('[name="monday_start"]').value;
            const mondayEnd = document.querySelector('[name="monday_end"]').value;
            const mondayBreakStart = document.querySelector('[name="monday_break_start"]').value;
            const mondayBreakEnd = document.querySelector('[name="monday_break_end"]').value;
            const mondayWorking = document.getElementById('monday_working').checked;

            ['tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
                document.getElementById(`${day}_working`).checked = mondayWorking;
                document.querySelector(`[name="${day}_start"]`).value = mondayStart;
                document.querySelector(`[name="${day}_end"]`).value = mondayEnd;
                document.querySelector(`[name="${day}_break_start"]`).value = mondayBreakStart;
                document.querySelector(`[name="${day}_break_end"]`).value = mondayBreakEnd;
                
                // Enable/disable inputs
                const inputs = document.querySelectorAll(`[name^="${day}_"]:not([name$="_working"])`);
                inputs.forEach(input => {
                    input.disabled = !mondayWorking;
                });
                
                updateDayHours(day);
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
                updateDayHours(day);
            });
        });
    </script>
    @endpush
    @endauthBoth
@endsection





