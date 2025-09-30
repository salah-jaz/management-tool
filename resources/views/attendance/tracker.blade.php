@extends('layout')
@section('title')
    Attendance Tracker
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Simple Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">Attendance Tracker</h3>
                        <p class="text-muted mb-0">Track your daily attendance and work hours</p>
                    </div>
                    <div class="text-end">
                        <div class="h4 mb-1" id="currentTime"></div>
                        <div class="text-muted" id="currentDate"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Attendance Card -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="me-3">
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bx bx-time text-white" id="statusIcon" style="font-size: 1.5rem;"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 id="statusText" class="mb-1">Not Checked In</h4>
                                        <p class="text-muted mb-0" id="statusSubtext">Click check-in to start your day</p>
                                        <small class="text-muted" id="lastUpdated">Last updated: --:--</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded">
                                            <div class="text-muted small">Check In</div>
                                            <div class="h5 mb-0" id="checkInTime">--:--</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded">
                                            <div class="text-muted small">Check Out</div>
                                            <div class="h5 mb-0" id="checkOutTime">--:--</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded bg-primary text-white">
                                            <div class="small">Work Hours</div>
                                            <div class="h5 mb-0" id="workHours">00:00</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 border rounded">
                                            <div class="text-muted small">Break Time</div>
                                            <div class="h5 mb-0" id="breakTime">00:00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" id="checkInBtn" onclick="checkIn()">
                                <i class="bx bx-log-in me-2"></i>Check In
                            </button>
                            <button class="btn btn-danger btn-lg" id="checkOutBtn" onclick="checkOut()" style="display: none;">
                                <i class="bx bx-log-out me-2"></i>Check Out
                            </button>
                            <button class="btn btn-warning btn-lg" id="startBreakBtn" onclick="startBreak()" style="display: none;">
                                <i class="bx bx-pause me-2"></i>Start Break
                            </button>
                            <button class="btn btn-info btn-lg" id="endBreakBtn" onclick="endBreak()" style="display: none;">
                                <i class="bx bx-play me-2"></i>End Break
                            </button>
                        </div>
                        <div class="mt-3" id="breakInfo" style="display: none;">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <div class="text-muted small">Break Duration</div>
                                <div class="h4 mb-0" id="breakTimer">00:00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Breaks -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-coffee me-2"></i>
                            Today's Breaks
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="breaksTable">
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
                                <tbody id="breaksTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No breaks recorded today</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Summary -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-bar-chart me-2"></i>
                            This Week's Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="weeklySummary">
                            <!-- Weekly summary will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Break Modal -->
    <div class="modal fade" id="breakModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-coffee me-2"></i>
                        Start Break
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="breakForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Break Type</label>
                            <select name="break_type" class="form-select" required>
                                <option value="lunch">Lunch</option>
                                <option value="coffee">Coffee Break</option>
                                <option value="personal">Personal</option>
                                <option value="meeting">Meeting</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <textarea name="break_reason" class="form-control" rows="3" placeholder="Brief reason for the break..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Start Break</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentStatus = 'not_checked_in';
        let breakTimer = null;
        let breakStartTime = null;

        // Update current time
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: false, 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            const dateString = now.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            document.getElementById('currentTime').textContent = timeString;
            document.getElementById('currentDate').textContent = dateString;
        }

        // Load current attendance status
        function loadCurrentStatus() {
            fetch('{{ route("attendance.current-status") }}')
                .then(response => response.json())
                .then(data => {
                    currentStatus = data.status;
                    updateAttendanceUI(data);
                })
                .catch(error => console.error('Error loading status:', error));
        }

        // Update attendance UI based on status
        function updateAttendanceUI(data) {
            const statusCircle = document.getElementById('statusCircle');
            const statusIcon = document.getElementById('statusIcon');
            const statusText = document.getElementById('statusText');
            const statusSubtext = document.getElementById('statusSubtext');
            const lastUpdated = document.getElementById('lastUpdated');
            const checkInTime = document.getElementById('checkInTime');
            const checkOutTime = document.getElementById('checkOutTime');
            const workHours = document.getElementById('workHours');
            const breakTime = document.getElementById('breakTime');

            // Update status indicator
            switch(currentStatus) {
                case 'not_checked_in':
                    statusCircle.className = 'status-circle bg-secondary';
                    statusIcon.className = 'bx bx-time';
                    statusText.textContent = 'Not Checked In';
                    statusSubtext.textContent = 'Click check-in to start your day';
                    break;
                case 'checked_in':
                    statusCircle.className = 'status-circle bg-success';
                    statusIcon.className = 'bx bx-check-circle';
                    statusText.textContent = 'Checked In';
                    statusSubtext.textContent = 'You are currently working';
                    break;
                case 'on_break':
                    statusCircle.className = 'status-circle bg-warning';
                    statusIcon.className = 'bx bx-coffee';
                    statusText.textContent = 'On Break';
                    statusSubtext.textContent = 'You are currently on break';
                    break;
                case 'checked_out':
                    statusCircle.className = 'status-circle bg-info';
                    statusIcon.className = 'bx bx-log-out';
                    statusText.textContent = 'Checked Out';
                    statusSubtext.textContent = 'You have completed your work day';
                    break;
            }

            // Update times
            if (data.attendance) {
                checkInTime.textContent = data.attendance.check_in_time_formatted || '--:--';
                checkOutTime.textContent = data.attendance.check_out_time_formatted || '--:--';
                workHours.textContent = data.attendance.total_work_hours_formatted || '00:00';
                breakTime.textContent = data.attendance.total_break_hours_formatted || '00:00';
            }

            // Update last updated time
            const now = new Date();
            lastUpdated.textContent = `Last updated: ${now.toLocaleTimeString('en-US', { 
                hour12: true, 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            })}`;

            // Update buttons
            updateActionButtons();
            updateBreaksTable(data.attendance);
        }

        // Update action buttons based on status
        function updateActionButtons() {
            const checkInBtn = document.getElementById('checkInBtn');
            const checkOutBtn = document.getElementById('checkOutBtn');
            const startBreakBtn = document.getElementById('startBreakBtn');
            const endBreakBtn = document.getElementById('endBreakBtn');
            const breakInfo = document.getElementById('breakInfo');

            // Hide all buttons first
            checkInBtn.style.display = 'none';
            checkOutBtn.style.display = 'none';
            startBreakBtn.style.display = 'none';
            endBreakBtn.style.display = 'none';
            breakInfo.style.display = 'none';

            switch(currentStatus) {
                case 'not_checked_in':
                    checkInBtn.style.display = 'block';
                    break;
                case 'checked_in':
                    checkOutBtn.style.display = 'block';
                    startBreakBtn.style.display = 'block';
                    break;
                case 'on_break':
                    endBreakBtn.style.display = 'block';
                    breakInfo.style.display = 'block';
                    startBreakTimer();
                    break;
                case 'checked_out':
                    // No buttons for checked out status
                    break;
            }
        }

        // Check in function
        function checkIn() {
            fetch('{{ route("attendance.check-in") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCurrentStatus();
                    showNotification('Checked in successfully!', 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error checking in', 'error');
            });
        }

        // Check out function
        function checkOut() {
            if (confirm('Are you sure you want to check out?')) {
                fetch('{{ route("attendance.check-out") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCurrentStatus();
                        showNotification('Checked out successfully!', 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error checking out', 'error');
                });
            }
        }

        // Start break function
        function startBreak() {
            const modal = new bootstrap.Modal(document.getElementById('breakModal'));
            modal.show();
        }

        // End break function
        function endBreak() {
            fetch('{{ route("attendance.end-break") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCurrentStatus();
                    showNotification('Break ended successfully!', 'success');
                    stopBreakTimer();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error ending break', 'error');
            });
        }

        // Start break timer
        function startBreakTimer() {
            breakStartTime = new Date();
            breakTimer = setInterval(updateBreakTimer, 1000);
        }

        // Stop break timer
        function stopBreakTimer() {
            if (breakTimer) {
                clearInterval(breakTimer);
                breakTimer = null;
            }
            document.getElementById('breakTimer').textContent = '00:00';
        }

        // Update break timer display
        function updateBreakTimer() {
            if (breakStartTime) {
                const now = new Date();
                const diff = now - breakStartTime;
                const minutes = Math.floor(diff / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                document.getElementById('breakTimer').textContent = 
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }

        // Update breaks table
        function updateBreaksTable(attendance) {
            const tbody = document.getElementById('breaksTableBody');
            if (!attendance || !attendance.breaks || attendance.breaks.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No breaks recorded today</td></tr>';
                return;
            }

            let html = '';
            attendance.breaks.forEach(breakItem => {
                html += `
                    <tr>
                        <td><span class="badge bg-${breakItem.type_badge}">${breakItem.break_type}</span></td>
                        <td>${breakItem.break_start_time_formatted || '--:--'}</td>
                        <td>${breakItem.break_end_time_formatted || '--:--'}</td>
                        <td>${breakItem.break_duration_formatted || '00:00'}</td>
                        <td>${breakItem.break_reason || '-'}</td>
                        <td><span class="badge bg-${breakItem.status_badge}">${breakItem.break_status}</span></td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // Show notification
        function showNotification(message, type) {
            // You can implement your preferred notification system here
            alert(message);
        }

        // Break form submission
        document.getElementById('breakForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('{{ route("attendance.start-break") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('breakModal')).hide();
                    loadCurrentStatus();
                    showNotification('Break started successfully!', 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error starting break', 'error');
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);
            loadCurrentStatus();
            setInterval(loadCurrentStatus, 30000); // Refresh every 30 seconds
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75rem;
        }
    </style>
    @endpush
    @endauthBoth
@endsection

