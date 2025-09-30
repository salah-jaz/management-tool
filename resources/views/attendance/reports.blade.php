@extends('layout')
@section('title')
    <?= get_label('attendance_reports', 'Attendance Reports') ?>
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card modern-reports-header">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-1">
                                    <i class="bx bx-bar-chart me-2"></i>
                                    <?= get_label('attendance_reports', 'Attendance Reports') ?>
                                </h4>
                                <p class="text-muted mb-0">Comprehensive attendance analytics and reporting</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="exportReport()">
                                    <i class="bx bx-download me-1"></i>
                                    <?= get_label('export_report', 'Export Report') ?>
                                </button>
                                <button type="button" class="btn btn-primary" onclick="printReport()">
                                    <i class="bx bx-printer me-1"></i>
                                    <?= get_label('print', 'Print') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('attendance.reports') }}" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label"><?= get_label('user', 'User') ?></label>
                                    <select name="user_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                        <option value=""><?= get_label('all_users', 'All Users') ?></option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><?= get_label('start_date', 'Start Date') ?></label>
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" onchange="document.getElementById('filterForm').submit()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><?= get_label('end_date', 'End Date') ?></label>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" onchange="document.getElementById('filterForm').submit()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><?= get_label('status', 'Status') ?></label>
                                    <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                        <option value=""><?= get_label('all_status', 'All Status') ?></option>
                                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                        <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-success">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Present Days</h6>
                                <h4 class="mb-0 text-success">{{ $summary['present_days'] }}</h4>
                                <small class="text-muted">Attendance Rate: {{ $summary['attendance_rate'] }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-danger">
                                <i class="bx bx-x-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Absent Days</h6>
                                <h4 class="mb-0 text-danger">{{ $summary['absent_days'] }}</h4>
                                <small class="text-muted">Absence Rate: {{ round(100 - $summary['attendance_rate'], 2) }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-warning">
                                <i class="bx bx-time"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Late Days</h6>
                                <h4 class="mb-0 text-warning">{{ $summary['late_days'] }}</h4>
                                <small class="text-muted">Punctuality: {{ $summary['punctuality_rate'] }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="summary-icon bg-info">
                                <i class="bx bx-clock"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Total Work Hours</h6>
                                <h4 class="mb-0 text-info">{{ $summary['total_work_hours'] }}h</h4>
                                <small class="text-muted">Avg: {{ $summary['average_work_hours'] }}h/day</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-line-chart me-2"></i>
                            Daily Attendance Trend
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyAttendanceChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-pie-chart me-2"></i>
                            Status Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusDistributionChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-ul me-2"></i>
                            Detailed Attendance Records
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?= get_label('user', 'User') ?></th>
                                        <th><?= get_label('date', 'Date') ?></th>
                                        <th><?= get_label('check_in', 'Check In') ?></th>
                                        <th><?= get_label('check_out', 'Check Out') ?></th>
                                        <th><?= get_label('work_hours', 'Work Hours') ?></th>
                                        <th><?= get_label('break_hours', 'Break Hours') ?></th>
                                        <th><?= get_label('overtime', 'Overtime') ?></th>
                                        <th><?= get_label('status', 'Status') ?></th>
                                        <th><?= get_label('approval', 'Approval') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendances as $attendance)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <img src="{{ $attendance->user->avatar ?? asset('photos/default-avatar.png') }}" 
                                                             class="rounded-circle" width="32" height="32" alt="Avatar">
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $attendance->user->name }}</h6>
                                                        <small class="text-muted">{{ $attendance->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $attendance->attendance_date->format('M d, Y') }}</td>
                                            <td>
                                                @if($attendance->check_in_time)
                                                    <span class="badge bg-success">{{ $attendance->check_in_time_formatted }}</span>
                                                @else
                                                    <span class="badge bg-danger">Not Checked In</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance->check_out_time)
                                                    <span class="badge bg-info">{{ $attendance->check_out_time_formatted }}</span>
                                                @else
                                                    <span class="badge bg-warning">Not Checked Out</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ $attendance->total_work_hours_formatted }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $attendance->total_break_hours_formatted }}</span>
                                            </td>
                                            <td>
                                                @if($attendance->overtime_hours > 0)
                                                    <span class="text-success">+{{ $attendance->overtime_hours }}h</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $attendance->status_badge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                                </span>
                                                @if($attendance->late_minutes > 0)
                                                    <br><small class="text-warning">{{ $attendance->late_minutes }}m late</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance->is_approved)
                                                    <span class="badge bg-success">
                                                        <i class="bx bx-check me-1"></i>Approved
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="bx bx-time me-1"></i>Pending
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bx bx-bar-chart display-4"></i>
                                                    <p class="mt-2">No attendance records found for the selected period</p>
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

        <!-- User Summary Reports -->
        @php($userReportsCount = is_countable($userReports) ? count($userReports) : (method_exists($userReports, 'count') ? $userReports->count() : 0))
        @if($userReportsCount > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-user me-2"></i>
                            Individual User Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($userReports as $userReport)
                                <div class="col-md-6 mb-4">
                                    <div class="user-report-card">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar-sm me-3">
                                                <img src="{{ $userReport['user']->avatar ?? asset('photos/default-avatar.png') }}" 
                                                     class="rounded-circle" width="40" height="40" alt="Avatar">
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $userReport['user']->name }}</h6>
                                                <small class="text-muted">{{ $userReport['user']->email }}</small>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <span class="label">Present:</span>
                                                    <span class="value text-success">{{ $userReport['summary']['present_days'] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <span class="label">Absent:</span>
                                                    <span class="value text-danger">{{ $userReport['summary']['absent_days'] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <span class="label">Late:</span>
                                                    <span class="value text-warning">{{ $userReport['summary']['late_days'] }}</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <span class="label">Work Hours:</span>
                                                    <span class="value text-info">{{ $userReport['summary']['total_work_hours'] }}h</span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="stat-item">
                                                    <span class="label">Attendance Rate:</span>
                                                    <span class="value text-primary">{{ $userReport['summary']['attendance_rate'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daily Attendance Chart
        const dailyCtx = document.getElementById('dailyAttendanceChart').getContext('2d');
        const dailyData = @json($chartsData['daily']);
        
        const dailyLabels = Object.keys(dailyData);
        const presentData = Object.values(dailyData).map(day => day.present);
        const absentData = Object.values(dailyData).map(day => day.absent);
        const lateData = Object.values(dailyData).map(day => day.late);
        const halfDayData = Object.values(dailyData).map(day => day.half_day);
        const leaveData = Object.values(dailyData).map(day => day.leave);

        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [
                    {
                        label: 'Present',
                        data: presentData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Absent',
                        data: absentData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Late',
                        data: lateData,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Half Day',
                        data: halfDayData,
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Leave',
                        data: leaveData,
                        borderColor: '#6c757d',
                        backgroundColor: 'rgba(108, 117, 125, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
        const statusData = @json($chartsData['status_distribution']);

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late', 'Half Day', 'Leave'],
                datasets: [{
                    data: [
                        statusData.present,
                        statusData.absent,
                        statusData.late,
                        statusData.half_day,
                        statusData.leave
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#dc3545',
                        '#ffc107',
                        '#17a2b8',
                        '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Export report
        function exportReport() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', '1');
            window.open(`{{ route('attendance.reports') }}?${params.toString()}`, '_blank');
        }

        // Print report
        function printReport() {
            window.print();
        }
    </script>
    @endpush

    @push('styles')
    <style>
        .modern-reports-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border: none;
        }

        .modern-reports-header .card-title {
            color: white;
        }

        .modern-reports-header .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .summary-card {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
        }

        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .user-report-card {
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-item .label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .stat-item .value {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .avatar-sm img {
            object-fit: cover;
        }

        .badge {
            font-size: 0.75rem;
        }

        @media print {
            .btn, .card-header .btn {
                display: none !important;
            }
            
            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
        }
    </style>
    @endpush
    @endauthBoth
@endsection

