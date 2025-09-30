@extends('layout')
@section('title')
    <?= get_label('attendance_management', 'Attendance Management') ?>
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
                <!-- Simple Header -->
        <div class="row mb-4">
            <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">Attendance Management</h3>
                                <p class="text-muted mb-0">Track employee attendance and work hours</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                                    <i class="bx bx-plus me-1"></i>Add Record
                                </button>
                                <a href="{{ route('attendance.tracker') }}" class="btn btn-success">
                                    <i class="bx bx-time-five me-1"></i>Quick Tracker
                                </a>
                                <a href="{{ route('attendance.working-hours.index') }}" class="btn btn-outline-info">
                                    <i class="bx bx-time me-1"></i>Working Hours
                                </a>
                                <button type="button" class="btn btn-outline-secondary" onclick="exportAttendance()">
                                    <i class="bx bx-download me-1"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Bar -->
        <div class="row mb-4">
            <div class="col-12">
                        <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                        <h6 class="mb-1">Quick Actions</h6>
                                        <small class="text-muted">Common attendance tasks</small>
                            </div>
                            <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="bulkApprove()">
                                            <i class="bx bx-check-double me-1"></i>Bulk Approve
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="bulkEdit()">
                                            <i class="bx bx-edit me-1"></i>Bulk Edit
                                </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="bulkDelete()">
                                            <i class="bx bx-trash me-1"></i>Bulk Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simple Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-success mb-1">{{ $attendances->where('status', 'present')->count() }}</h4>
                        <p class="text-muted mb-0">Present Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-danger mb-1">{{ $attendances->where('status', 'absent')->count() }}</h4>
                        <p class="text-muted mb-0">Absent Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-warning mb-1">{{ $attendances->where('status', 'late')->count() }}</h4>
                        <p class="text-muted mb-0">Late Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-info mb-1">{{ number_format($totalHours, 1) }}h</h4>
                        <p class="text-muted mb-0">Total Hours</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simple Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('attendance.index') }}" class="row g-3">
                                <div class="col-md-3">
                                <label class="form-label">Employee</label>
                                <select name="user_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Employees</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('attendance.index') }}'">Clear</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Attendance Records</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Hours</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendances as $attendance)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                        <img src="{{ $attendance->user->avatar ?? asset('photos/default-avatar.png') }}" 
                                                         class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                                    <div>
                                                        <div class="fw-medium">{{ $attendance->user->name }}</div>
                                                        <small class="text-muted">{{ $attendance->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $attendance->attendance_date->format('M d, Y') }}</td>
                                            <td>
                                                @if($attendance->check_in_time)
                                                    <span class="text-success">{{ $attendance->check_in_time_formatted }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance->check_out_time)
                                                    <span class="text-info">{{ $attendance->check_out_time_formatted }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ $attendance->total_work_hours_formatted }}</span>
                                                @if($attendance->overtime_hours > 0)
                                                    <small class="text-success">(+{{ $attendance->overtime_hours }}h)</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $attendance->status_badge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 action-buttons">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewAttendance({{ $attendance->id }})" title="View Details">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" onclick="editAttendance({{ $attendance->id }})" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    @if(!$attendance->is_approved)
                                                        <button class="btn btn-sm btn-outline-success" onclick="approveAttendance({{ $attendance->id }})" title="Approve">
                                                            <i class="bx bx-check"></i>
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAttendance({{ $attendance->id }})" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bx bx-time-five display-4"></i>
                                                    <p class="mt-2">No attendance records found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="text-muted">
                                Showing {{ $attendances->firstItem() ?? 0 }} to {{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }} records
                            </div>
                            <div>
                            {{ $attendances->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Attendance Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select Employee</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="attendance_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Check In Time <span class="text-danger">*</span></label>
                                <input type="time" name="check_in_time" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Check Out Time</label>
                                <input type="time" name="check_out_time" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // View attendance details
        window.viewAttendance = function(attendanceId) {
            window.open(`/attendance/${attendanceId}`, '_blank');
        }

        // Edit attendance
        window.editAttendance = function(attendanceId) {
            window.location.href = `/attendance/${attendanceId}/edit`;
        }

        // Approve attendance with better feedback
        window.approveAttendance = function(attendanceId) {
            if (confirm('Approve this attendance record?')) {
                showLoading('Approving...');
                fetch(`/attendance/${attendanceId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showNotification('Attendance approved successfully!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showNotification('Error approving attendance', 'error');
                });
            }
        }

        // Delete attendance with better feedback (robust with fallback)
        window.deleteAttendance = function(attendanceId) {
            console.log('Attempting to delete attendance ID:', attendanceId); // Debug log
            if (!confirm('Are you sure you want to delete this attendance record? This action cannot be undone.')) return;

            showLoading('Deleting...');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Primary attempt: true DELETE request
                fetch(`/attendance/${attendanceId}`, {
                    method: 'DELETE',
                    headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(async (response) => {
                console.log('Delete response status:', response.status);
                if (!response.ok) {
                    // Fallback: some servers block DELETE; try POST + _method spoofing
                    console.warn('DELETE failed, retrying with POST + _method=DELETE');
                    return fetch(`/attendance/${attendanceId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ _method: 'DELETE' })
                    });
                }
                return response;
            })
            .then(res => res.json())
            .then(data => {
                console.log('Delete response data:', data);
                hideLoading();
                if (data && data.success) {
                    showNotification('Attendance deleted successfully!', 'success');
                    setTimeout(() => location.reload(), 800);
                    } else {
                    const message = (data && data.message) ? data.message : 'Unknown error while deleting';
                    showNotification('Error: ' + message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Delete error:', error);
                showNotification('Error deleting attendance: ' + (error?.message || 'Unexpected error'), 'error');
            });
        }

        // Show loading indicator
        function showLoading(message) {
            const loadingHtml = `
                <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
                    <div class="bg-white p-3 rounded shadow">
                        <div class="spinner-border text-primary me-2" role="status"></div>
                        ${message}
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loadingHtml);
        }

        // Hide loading indicator
        function hideLoading() {
            const loading = document.getElementById('loadingOverlay');
            if (loading) {
                loading.remove();
            }
        }

        // Show notification
        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 10000;">
                    <i class="bx ${icon} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        }

        // Bulk actions
        function bulkApprove() {
            const selectedIds = getSelectedAttendanceIds();
            if (selectedIds.length === 0) {
                showNotification('Please select attendance records to approve', 'error');
                return;
            }
            
            if (confirm(`Approve ${selectedIds.length} attendance records?`)) {
                showLoading('Approving records...');
                // Implementation for bulk approve
                showNotification('Bulk approve feature coming soon!', 'info');
                hideLoading();
            }
        }

        function bulkEdit() {
            const selectedIds = getSelectedAttendanceIds();
            if (selectedIds.length === 0) {
                showNotification('Please select attendance records to edit', 'error');
                return;
            }
            
            showNotification('Bulk edit feature coming soon!', 'info');
        }

        function bulkDelete() {
            const selectedIds = getSelectedAttendanceIds();
            if (selectedIds.length === 0) {
                showNotification('Please select attendance records to delete', 'error');
                return;
            }
            
            if (confirm(`Delete ${selectedIds.length} attendance records? This action cannot be undone.`)) {
                showLoading('Deleting records...');
                // Implementation for bulk delete
                showNotification('Bulk delete feature coming soon!', 'info');
                hideLoading();
            }
        }

        function getSelectedAttendanceIds() {
            // This would get selected checkboxes - for now return empty array
            return [];
        }

        // Export attendance
        function exportAttendance() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', '1');
            window.open(`{{ route('attendance.index') }}?${params.toString()}`, '_blank');
        }
    </script>

    @push('styles')
    <style>
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .avatar-sm img {
            object-fit: cover;
        }

        .badge {
            font-size: 0.75rem;
        }

        /* Action buttons styling */
        .action-buttons .btn {
            min-width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-buttons .btn i {
            font-size: 14px;
        }

        /* Quick actions bar */
        .quick-actions {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .quick-actions .btn {
            border-color: rgba(255,255,255,0.3);
            color: white;
        }

        .quick-actions .btn:hover {
            background-color: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
        }

        /* Loading overlay */
        #loadingOverlay {
            backdrop-filter: blur(2px);
        }

        /* Notification styling */
        .alert {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: none;
        }
    </style>
    @endpush
    @endauthBoth
@endsection

