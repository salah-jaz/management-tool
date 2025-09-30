@extends('layout')
@section('title')
    <?= get_label('break_management', 'Break Management') ?>
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card modern-break-header">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-1">
                                    <i class="bx bx-coffee me-2"></i>
                                    <?= get_label('break_management', 'Break Management') ?>
                                </h4>
                                <p class="text-muted mb-0">Manage your breaks and track break time efficiently</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-warning" id="startBreakBtn" onclick="startBreak()">
                                    <i class="bx bx-pause me-1"></i>
                                    <?= get_label('start_break', 'Start Break') ?>
                                </button>
                                <button type="button" class="btn btn-info" id="endBreakBtn" onclick="endBreak()" style="display: none;">
                                    <i class="bx bx-play me-1"></i>
                                    <?= get_label('end_break', 'End Break') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="break-status">
                                    <div class="d-flex align-items-center">
                                        <div class="status-indicator me-3" id="breakStatusIndicator">
                                            <div class="status-circle bg-secondary" id="breakStatusCircle"></div>
                                        </div>
                                        <div>
                                            <h5 class="mb-0" id="breakStatusText">No Active Break</h5>
                                            <p class="text-muted mb-0" id="breakStatusSubtext">Click start break to begin</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="break-timer-section" id="breakTimerSection" style="display: none;">
                                    <div class="text-center">
                                        <h6 class="text-muted mb-2">Break Duration</h6>
                                        <div class="break-timer-display">
                                            <span class="timer-text" id="breakTimerDisplay">00:00</span>
                                        </div>
                                        <small class="text-muted">Started at: <span id="breakStartTime">--:--</span></small>
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
                        <h6 class="card-title">Today's Break Summary</h6>
                        <div class="break-summary">
                            <div class="summary-item">
                                <span class="label">Total Breaks:</span>
                                <span class="value" id="totalBreaks">0</span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Total Time:</span>
                                <span class="value" id="totalBreakTime">00:00</span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Remaining:</span>
                                <span class="value" id="remainingBreakTime">120 min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Break Types Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-coffee me-2"></i>
                            Quick Break Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary w-100 break-type-btn" onclick="quickStartBreak('lunch')">
                                    <i class="bx bx-restaurant d-block mb-2"></i>
                                    <span>Lunch</span>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-info w-100 break-type-btn" onclick="quickStartBreak('coffee')">
                                    <i class="bx bx-coffee d-block mb-2"></i>
                                    <span>Coffee</span>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-warning w-100 break-type-btn" onclick="quickStartBreak('personal')">
                                    <i class="bx bx-user d-block mb-2"></i>
                                    <span>Personal</span>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-success w-100 break-type-btn" onclick="quickStartBreak('meeting')">
                                    <i class="bx bx-group d-block mb-2"></i>
                                    <span>Meeting</span>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary w-100 break-type-btn" onclick="quickStartBreak('other')">
                                    <i class="bx bx-dots-horizontal d-block mb-2"></i>
                                    <span>Other</span>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-danger w-100" onclick="showBreakModal()">
                                    <i class="bx bx-plus d-block mb-2"></i>
                                    <span>Custom</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Breaks -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-ul me-2"></i>
                            Today's Breaks
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="breaksTable">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="breaksTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-coffee display-4"></i>
                                                <p class="mt-2">No breaks recorded today</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Break Modal -->
    <div class="modal fade" id="customBreakModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-plus me-2"></i>
                        Start Custom Break
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="customBreakForm">
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
                            <label class="form-label">Reason</label>
                            <textarea name="break_reason" class="form-control" rows="3" placeholder="Brief reason for the break..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expected Duration (minutes)</label>
                            <input type="number" name="expected_duration" class="form-control" placeholder="e.g., 30" min="1" max="240">
                            <small class="text-muted">Optional: Set expected break duration</small>
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
        let currentBreakStatus = 'no_break';
        let breakTimer = null;
        let breakStartTime = null;
        let expectedDuration = null;

        // Load current break status
        function loadBreakStatus() {
            fetch('{{ route("attendance.current-status") }}')
                .then(response => response.json())
                .then(data => {
                    currentBreakStatus = data.status;
                    updateBreakUI(data);
                    updateBreakSummary(data.attendance);
                })
                .catch(error => console.error('Error loading break status:', error));
        }

        // Update break UI
        function updateBreakUI(data) {
            const statusCircle = document.getElementById('breakStatusCircle');
            const statusText = document.getElementById('breakStatusText');
            const statusSubtext = document.getElementById('breakStatusSubtext');
            const startBtn = document.getElementById('startBreakBtn');
            const endBtn = document.getElementById('endBreakBtn');
            const timerSection = document.getElementById('breakTimerSection');

            if (data.active_break) {
                // User is on break
                statusCircle.className = 'status-circle bg-warning';
                statusText.textContent = 'On Break';
                statusSubtext.textContent = `${data.active_break.break_type} break in progress`;
                startBtn.style.display = 'none';
                endBtn.style.display = 'block';
                timerSection.style.display = 'block';
                
                // Start timer
                breakStartTime = new Date(data.active_break.break_start_time);
                startBreakTimer();
            } else if (data.attendance && data.attendance.check_in_time && !data.attendance.check_out_time) {
                // User is checked in but not on break
                statusCircle.className = 'status-circle bg-success';
                statusText.textContent = 'Ready for Break';
                statusSubtext.textContent = 'You are checked in and can take a break';
                startBtn.style.display = 'block';
                endBtn.style.display = 'none';
                timerSection.style.display = 'none';
            } else {
                // User is not checked in
                statusCircle.className = 'status-circle bg-secondary';
                statusText.textContent = 'Not Checked In';
                statusSubtext.textContent = 'You must check in first to take breaks';
                startBtn.style.display = 'none';
                endBtn.style.display = 'none';
                timerSection.style.display = 'none';
            }

            // Update breaks table
            updateBreaksTable(data.attendance);
        }

        // Update break summary
        function updateBreakSummary(attendance) {
            if (!attendance || !attendance.breaks) {
                document.getElementById('totalBreaks').textContent = '0';
                document.getElementById('totalBreakTime').textContent = '00:00';
                document.getElementById('remainingBreakTime').textContent = '120 min';
                return;
            }

            const completedBreaks = attendance.breaks.filter(breakItem => breakItem.break_status === 'completed');
            const totalBreaks = completedBreaks.length + (attendance.breaks.some(b => b.break_status === 'active') ? 1 : 0);
            
            // Calculate total break time
            let totalMinutes = 0;
            completedBreaks.forEach(breakItem => {
                if (breakItem.break_duration) {
                    const [hours, minutes] = breakItem.break_duration.split(':');
                    totalMinutes += parseInt(hours) * 60 + parseInt(minutes);
                }
            });

            const totalHours = Math.floor(totalMinutes / 60);
            const remainingMinutes = totalMinutes % 60;
            const totalTimeFormatted = `${totalHours.toString().padStart(2, '0')}:${remainingMinutes.toString().padStart(2, '0')}`;

            document.getElementById('totalBreaks').textContent = totalBreaks;
            document.getElementById('totalBreakTime').textContent = totalTimeFormatted;
            document.getElementById('remainingBreakTime').textContent = `${Math.max(0, 120 - totalMinutes)} min`;
        }

        // Start break timer
        function startBreakTimer() {
            if (breakTimer) clearInterval(breakTimer);
            
            breakTimer = setInterval(() => {
                if (breakStartTime) {
                    const now = new Date();
                    const diff = now - breakStartTime;
                    const minutes = Math.floor(diff / 60000);
                    const seconds = Math.floor((diff % 60000) / 1000);
                    
                    document.getElementById('breakTimerDisplay').textContent = 
                        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }

        // Stop break timer
        function stopBreakTimer() {
            if (breakTimer) {
                clearInterval(breakTimer);
                breakTimer = null;
            }
            document.getElementById('breakTimerDisplay').textContent = '00:00';
        }

        // Start break function
        function startBreak() {
            showBreakModal();
        }

        // Quick start break
        function quickStartBreak(breakType) {
            fetch('{{ route("attendance.start-break") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    break_type: breakType,
                    break_reason: `${breakType.charAt(0).toUpperCase() + breakType.slice(1)} break`
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadBreakStatus();
                    showNotification(`Break started successfully!`, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error starting break', 'error');
            });
        }

        // End break function
        function endBreak() {
            if (confirm('Are you sure you want to end your break?')) {
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
                        loadBreakStatus();
                        stopBreakTimer();
                        showNotification('Break ended successfully!', 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error ending break', 'error');
                });
            }
        }

        // Show break modal
        function showBreakModal() {
            const modal = new bootstrap.Modal(document.getElementById('customBreakModal'));
            modal.show();
        }

        // Update breaks table
        function updateBreaksTable(attendance) {
            const tbody = document.getElementById('breaksTableBody');
            if (!attendance || !attendance.breaks || attendance.breaks.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <div class="text-muted">
                                <i class="bx bx-coffee display-4"></i>
                                <p class="mt-2">No breaks recorded today</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            attendance.breaks.forEach(breakItem => {
                const typeBadge = getBreakTypeBadge(breakItem.break_type);
                const statusBadge = getBreakStatusBadge(breakItem.break_status);
                
                html += `
                    <tr>
                        <td><span class="badge ${typeBadge}">${breakItem.break_type}</span></td>
                        <td>${breakItem.break_start_time_formatted || '--:--'}</td>
                        <td>${breakItem.break_end_time_formatted || '--:--'}</td>
                        <td>${breakItem.break_duration_formatted || '00:00'}</td>
                        <td>${breakItem.break_reason || '-'}</td>
                        <td><span class="badge ${statusBadge}">${breakItem.break_status}</span></td>
                        <td>
                            ${breakItem.break_status === 'active' ? 
                                '<button class="btn btn-sm btn-danger" onclick="endBreak()"><i class="bx bx-stop"></i></button>' : 
                                '<span class="text-muted">-</span>'
                            }
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // Get break type badge class
        function getBreakTypeBadge(type) {
            const badges = {
                'lunch': 'bg-primary',
                'coffee': 'bg-info',
                'personal': 'bg-warning',
                'meeting': 'bg-success',
                'other': 'bg-secondary'
            };
            return badges[type] || 'bg-secondary';
        }

        // Get break status badge class
        function getBreakStatusBadge(status) {
            const badges = {
                'active': 'bg-warning',
                'completed': 'bg-success',
                'cancelled': 'bg-danger'
            };
            return badges[status] || 'bg-secondary';
        }

        // Show notification
        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        }

        // Custom break form submission
        document.getElementById('customBreakForm').addEventListener('submit', function(e) {
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
                    bootstrap.Modal.getInstance(document.getElementById('customBreakModal')).hide();
                    loadBreakStatus();
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
            loadBreakStatus();
            setInterval(loadBreakStatus, 30000); // Refresh every 30 seconds
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .modern-break-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
            color: white;
            border: none;
        }

        .modern-break-header .card-title {
            color: white;
        }

        .modern-break-header .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .status-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .break-timer-display {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ffc107;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .break-summary {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .summary-item .label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .summary-item .value {
            font-weight: bold;
            color: #495057;
        }

        .break-type-btn {
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .break-type-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .break-type-btn i {
            font-size: 2rem;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }

        .badge {
            font-size: 0.75rem;
        }
    </style>
    @endpush
    @endauthBoth
@endsection

