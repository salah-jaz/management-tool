@extends('layout')
@section('title')
    <?= get_label('dashboard', 'Dashboard') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Alert for Reset Warning -->
        @if (config('constants.ALLOW_MODIFICATION') === 0)
            <x-dashboard.alert type="warning" classes="container mb-0 mt-4" icon="bx bx-timer"
                message="Important: Data automatically resets every 24 hours!" dismissible="true" />
        @endif
        @php
                $tiles = [
                    'manage_projects' => [
                        'permission' => 'manage_projects',
                        'icon' => 'bx bx-briefcase-alt-2 text-success',
                        'icon-bg' => 'bg-label-success',
                        'label' => get_label('total_projects', 'Total projects'),
                        'count' => is_countable($projects) && count($projects) > 0 ? count($projects) : 0,
                        'url' => url(getUserPreferences('projects', 'default_view')),
                        'link_color' => 'text-success',
                    ],
                    'manage_tasks' => [
                        'permission' => 'manage_tasks',
                        'icon' => 'bx bx-task text-primary',
                        'icon-bg' => 'bg-label-primary',
                        'label' => get_label('total_tasks', 'Total tasks'),
                        'count' => $tasks,
                        'url' => url(getUserPreferences('tasks', 'default_view')),
                        'link_color' => 'text-primary',
                    ],
                    'manage_users' => [
                        'permission' => 'manage_users',
                        'icon' => 'bx bxs-user-detail text-warning',
                        'icon-bg' => 'bg-label-warning',
                        'label' => get_label('total_users', 'Total users'),
                        'count' => is_countable($users) && count($users) > 0 ? count($users) : 0,
                        'url' => url('users'),
                        'link_color' => 'text-warning',
                    ],
                    'manage_clients' => [
                        'permission' => 'manage_clients',
                        'icon' => 'bx bxs-user-detail text-info',
                        'icon-bg' => 'bg-label-info',
                        'label' => get_label('total_clients', 'Total clients'),
                        'count' => is_countable($clients) && count($clients) > 0 ? count($clients) : 0,
                        'url' => url('clients'),
                        'link_color' => 'text-info',
                    ],
                    'manage_meetings' => [
                        'permission' => 'manage_meetings',
                        'icon' => 'bx bx-shape-polygon text-warning',
                        'icon-bg' => 'bg-label-warning',
                        'label' => get_label('total_meetings', 'Total meetings'),
                        'count' => is_countable($meetings) && count($meetings) > 0 ? count($meetings) : 0,
                        'url' => url('meetings'),
                        'link_color' => 'text-warning',
                    ],
                    'total_todos' => [
                        'permission' => null, // No specific permission required
                        'icon' => 'bx bx-list-check text-info',
                        'icon-bg' => 'bg-label-info',
                        'label' => get_label('total_todos', 'Total todos'),
                        'count' => is_countable($total_todos) && count($total_todos) > 0 ? count($total_todos) : 0,
                        'url' => url('todos'),
                        'link_color' => 'text-info',
                    ],
                ];
                // Filter tiles based on user permissions
                $filteredTiles = array_filter($tiles, function ($tile) use ($auth_user) {
                    return !$tile['permission'] || $auth_user->can($tile['permission']);
                });
                // Get the first 4 tiles
                $filteredTiles = array_slice($filteredTiles, 0, 4);
            @endphp


        <!-- Tiles Section -->
        <div class="col-lg-12 col-md-12 order-1">

            <div class="row mt-4">
                @foreach ($filteredTiles as $tile)
                    <x-dashboard.tile :label="$tile['label']" :count="$tile['count']" :url="$tile['url']" :linkColor="$tile['link_color']"
                        :icon="$tile['icon']" :iconBg="$tile['icon-bg']" />
                @endforeach
            </div>
            <!-- Statistics Section -->

            <div class="row">
                <x-dashboard.statistics :todos="$todos" :activities="$activities" />
            </div>
            <!-- Tabs Section -->
            @if (
                !isClient() &&
                    ($auth_user->can('manage_users') || $auth_user->can('manage_projects') || $auth_user->can('manage_tasks')))
                <x-dashboard.tabs :users="$users" :projects="$projects" :tasks="$tasks" />
            @endif
        </div>
        <!-- ------------------------------------------- -->
        @php
            $titles = [];
            $project_counts = [];
            $task_counts = [];
            $bg_colors = [];
            $total_projects = 0;
            $total_tasks = 0;
            $total_todos = count($todos);
            $done_todos = 0;
            $pending_todos = 0;
            $todo_counts = [];
            $ran = [
                '#63ed7a',
                '#ffa426',
                '#fc544b',
                '#6777ef',
                '#FF00FF',
                '#53ff1a',
                '#ff3300',
                '#0000ff',
                '#00ffff',
                '#99ff33',
                '#003366',
                '#cc3300',
                '#ffcc00',
                '#ff9900',
                '#3333cc',
                '#ffff00',
                '#FF5733',
                '#33FF57',
                '#5733FF',
                '#FFFF33',
                '#A6A6A6',
                '#FF99FF',
                '#6699FF',
                '#666666',
                '#FF6600',
                '#9900CC',
                '#FF99CC',
                '#FFCC99',
                '#99CCFF',
                '#33CCCC',
                '#CCFFCC',
                '#99CC99',
                '#669999',
                '#CCCCFF',
                '#6666FF',
                '#FF6666',
                '#99CCCC',
                '#993366',
                '#339966',
                '#99CC00',
                '#CC6666',
                '#660033',
                '#CC99CC',
                '#CC3300',
                '#FFCCCC',
                '#6600CC',
                '#FFCC33',
                '#9933FF',
                '#33FF33',
                '#FFFF66',
                '#9933CC',
                '#3300FF',
                '#9999CC',
                '#0066FF',
                '#339900',
                '#666633',
                '#330033',
                '#FF9999',
                '#66FF33',
                '#6600FF',
                '#FF0033',
                '#009999',
                '#CC0000',
                '#999999',
                '#CC0000',
                '#CCCC00',
                '#00FF33',
                '#0066CC',
                '#66FF66',
                '#FF33FF',
                '#CC33CC',
                '#660099',
                '#663366',
                '#996666',
                '#6699CC',
                '#663399',
                '#9966CC',
                '#66CC66',
                '#0099CC',
                '#339999',
                '#00CCCC',
                '#CCCC99',
                '#FF9966',
                '#99FF00',
                '#66FF99',
                '#336666',
                '#00FF66',
                '#3366CC',
                '#CC00CC',
                '#00FF99',
                '#FF0000',
                '#00CCFF',
                '#000000',
                '#FFFFFF',
            ];
            foreach ($statuses as $status) {
                $project_count = isAdminOrHasAllDataAccess()
                    ? count($status->projects)
                    : $auth_user->status_projects($status->id)->count();
                array_push($project_counts, $project_count);
                $task_count = isAdminOrHasAllDataAccess()
                    ? count($status->tasks)
                    : $auth_user->status_tasks($status->id)->count();
                array_push($task_counts, $task_count);
                array_push($titles, "'" . $status->title . "'");
                $v = array_shift($ran);
                array_push($bg_colors, "'" . $v . "'");
                $total_projects += $project_count;
                $total_tasks += $task_count;
            }
            $titles = implode(',', $titles);
            $project_counts = implode(',', $project_counts);
            $task_counts = implode(',', $task_counts);
            $bg_colors = implode(',', $bg_colors);
            foreach ($todos as $todo) {
                $todo->is_completed ? ($done_todos += 1) : ($pending_todos += 1);
            }
            array_push($todo_counts, $done_todos);
            array_push($todo_counts, $pending_todos);
            $todo_counts = implode(',', $todo_counts);
        @endphp
        <script>
            var labels = [<?= $titles ?>];
            var project_data = [<?= $project_counts ?>];
            var task_data = [<?= $task_counts ?>];
            var bg_colors = [<?= $bg_colors ?>];
            var total_projects = [<?= $total_projects ?>];
            var total_tasks = [<?= $total_tasks ?>];
            var total_todos = [<?= $total_todos ?>];
            var todo_data = [<?= $todo_counts ?>];
            //labels
            var done = '<?= get_label('done', 'Done') ?>';
            var pending = '<?= get_label('pending', 'Pending') ?>';
            var total = '<?= get_label('total', 'Total') ?>';
        </script>
        
        <!-- Attendance Tracker Widget -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card dashboard-card-modern">
                    <div class="dashboard-card-header">
                        <div class="d-flex align-items-center">
                            <div class="card-icon-wrapper me-3">
                                <i class="bx bx-time-five"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">Attendance Tracker</h5>
                                <p class="text-muted mb-0">Track your daily attendance and work hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="attendance-status-widget">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="status-indicator me-3" id="dashboardStatusIndicator">
                                            <div class="status-circle bg-secondary" id="dashboardStatusCircle">
                                                <i class="bx bx-time" id="dashboardStatusIcon"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1" id="dashboardStatusText">Not Checked In</h5>
                                            <p class="text-muted mb-0" id="dashboardStatusSubtext">Click check-in to start your day</p>
                                            <small class="text-muted" id="dashboardLastUpdated">Last updated: --:--</small>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="time-display">
                                                <div class="time-icon">
                                                    <i class="bx bx-log-in"></i>
                                                </div>
                                                <div class="time-content">
                                                    <label>Check In</label>
                                                    <span id="dashboardCheckInTime">--:--</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="time-display">
                                                <div class="time-icon">
                                                    <i class="bx bx-log-out"></i>
                                                </div>
                                                <div class="time-content">
                                                    <label>Check Out</label>
                                                    <span id="dashboardCheckOutTime">--:--</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="time-display highlight">
                                                <div class="time-icon">
                                                    <i class="bx bx-time-five"></i>
                                                </div>
                                                <div class="time-content">
                                                    <label>Work Hours</label>
                                                    <span id="dashboardWorkHours">00:00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="attendance-actions">
                                    <button class="btn btn-success btn-sm mb-2 w-100" id="dashboardCheckInBtn" onclick="dashboardCheckIn()">
                                        <i class="bx bx-log-in me-1"></i>Check In
                                    </button>
                                    <button class="btn btn-danger btn-sm mb-2 w-100" id="dashboardCheckOutBtn" onclick="dashboardCheckOut()" style="display: none;">
                                        <i class="bx bx-log-out me-1"></i>Check Out
                                    </button>
                                    <button class="btn btn-warning btn-sm mb-2 w-100" id="dashboardStartBreakBtn" onclick="dashboardStartBreak()" style="display: none;">
                                        <i class="bx bx-pause me-1"></i>Start Break
                                    </button>
                                    <button class="btn btn-info btn-sm mb-2 w-100" id="dashboardEndBreakBtn" onclick="dashboardEndBreak()" style="display: none;">
                                        <i class="bx bx-play me-1"></i>End Break
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Last updated: <span id="lastUpdated">--</span></small>
                            <div class="d-flex gap-2">
                                <a href="{{ route('attendance.breaks') }}" class="btn btn-outline-warning btn-sm">
                                    <i class="bx bx-coffee me-1"></i>Break Management
                                </a>
                                <a href="{{ route('attendance.tracker') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-time-five me-1"></i>Full Tracker
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
        <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
        
        <!-- Attendance Dashboard Scripts -->
        <script>
            let dashboardCurrentStatus = 'not_checked_in';

            // Load current attendance status for dashboard
            function loadDashboardAttendanceStatus() {
                fetch('{{ route("attendance.current-status") }}')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Dashboard attendance data:', data); // Debug log
                        dashboardCurrentStatus = data.status;
                        updateDashboardAttendanceUI(data);
                        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
                    })
                    .catch(error => console.error('Error loading dashboard status:', error));
            }

            // Update dashboard attendance UI
            function updateDashboardAttendanceUI(data) {
                const statusCircle = document.getElementById('dashboardStatusCircle');
                const statusIcon = document.getElementById('dashboardStatusIcon');
                const statusText = document.getElementById('dashboardStatusText');
                const statusSubtext = document.getElementById('dashboardStatusSubtext');
                const lastUpdated = document.getElementById('dashboardLastUpdated');
                const checkInTime = document.getElementById('dashboardCheckInTime');
                const checkOutTime = document.getElementById('dashboardCheckOutTime');
                const workHours = document.getElementById('dashboardWorkHours');

                // Update status indicator
                switch(dashboardCurrentStatus) {
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
                    console.log('Attendance data:', data.attendance); // Debug log
                    checkInTime.textContent = data.attendance.check_in_time_formatted || '--:--';
                    checkOutTime.textContent = data.attendance.check_out_time_formatted || '--:--';
                    workHours.textContent = data.attendance.total_work_hours_formatted || '00:00';
                } else {
                    console.log('No attendance data found'); // Debug log
                    checkInTime.textContent = '--:--';
                    checkOutTime.textContent = '--:--';
                    workHours.textContent = '00:00';
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
                updateDashboardActionButtons();
            }

            // Update dashboard action buttons
            function updateDashboardActionButtons() {
                const checkInBtn = document.getElementById('dashboardCheckInBtn');
                const checkOutBtn = document.getElementById('dashboardCheckOutBtn');
                const startBreakBtn = document.getElementById('dashboardStartBreakBtn');
                const endBreakBtn = document.getElementById('dashboardEndBreakBtn');

                // Hide all buttons first
                checkInBtn.style.display = 'none';
                checkOutBtn.style.display = 'none';
                startBreakBtn.style.display = 'none';
                endBreakBtn.style.display = 'none';

                switch(dashboardCurrentStatus) {
                    case 'not_checked_in':
                        checkInBtn.style.display = 'block';
                        break;
                    case 'checked_in':
                        checkOutBtn.style.display = 'block';
                        startBreakBtn.style.display = 'block';
                        break;
                    case 'on_break':
                        endBreakBtn.style.display = 'block';
                        break;
                    case 'checked_out':
                        // No buttons for checked out status
                        break;
                }
            }

            // Dashboard attendance functions
            function dashboardCheckIn() {
                fetch('{{ route("attendance.check-in") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'workspace_id': '{{ session('workspace_id') }}'
                    },
                    body: JSON.stringify({})
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    const isJson = contentType.includes('application/json');
                    const data = isJson ? await response.json() : { success: false, message: response.status === 419 ? 'Session expired. Please refresh and try again.' : 'Unexpected response from server.' };
                    return { ok: response.ok, status: response.status, data };
                })
                .then(({ ok, status, data }) => {
                    console.log('Check-in response:', data); // Debug log
                    if (data.success) {
                        loadDashboardAttendanceStatus();
                        showNotification('Checked in successfully!', 'success');
                    } else {
                        const message = data && data.message ? data.message : (status === 422 ? 'Workspace or user not resolved. Reselect a workspace and try again.' : 'Error checking in');
                        showNotification(message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error checking in', 'error');
                });
            }

            function dashboardCheckOut() {
                if (confirm('Are you sure you want to check out?')) {
                    fetch('{{ route("attendance.check-out") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'workspace_id': '{{ session('workspace_id') }}'
                        },
                        body: JSON.stringify({})
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type') || '';
                        const isJson = contentType.includes('application/json');
                        const data = isJson ? await response.json() : { success: false, message: response.status === 419 ? 'Session expired. Please refresh and try again.' : 'Unexpected response from server.' };
                        return { ok: response.ok, status: response.status, data };
                    })
                    .then(({ ok, status, data }) => {
                        if (data.success) {
                            loadDashboardAttendanceStatus();
                            showNotification('Checked out successfully!', 'success');
                        } else {
                            const message = data && data.message ? data.message : (status === 422 ? 'Workspace or user not resolved. Reselect a workspace and try again.' : 'Error checking out');
                            showNotification(message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error checking out', 'error');
                    });
                }
            }

            function dashboardStartBreak() {
                // Simple break start without modal for dashboard
                fetch('{{ route("attendance.start-break") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'workspace_id': '{{ session('workspace_id') }}'
                    },
                    body: JSON.stringify({
                        break_type: 'other',
                        break_reason: 'Quick break'
                    })
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    const isJson = contentType.includes('application/json');
                    const data = isJson ? await response.json() : { success: false, message: response.status === 419 ? 'Session expired. Please refresh and try again.' : 'Unexpected response from server.' };
                    return { ok: response.ok, status: response.status, data };
                })
                .then(({ ok, status, data }) => {
                    if (data.success) {
                        loadDashboardAttendanceStatus();
                        showNotification('Break started!', 'success');
                    } else {
                        const message = data && data.message ? data.message : (status === 422 ? 'Workspace or user not resolved. Reselect a workspace and try again.' : 'Error starting break');
                        showNotification(message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error starting break', 'error');
                });
            }

            function dashboardEndBreak() {
                fetch('{{ route("attendance.end-break") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'workspace_id': '{{ session('workspace_id') }}'
                    },
                    body: JSON.stringify({})
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    const isJson = contentType.includes('application/json');
                    const data = isJson ? await response.json() : { success: false, message: response.status === 419 ? 'Session expired. Please refresh and try again.' : 'Unexpected response from server.' };
                    return { ok: response.ok, status: response.status, data };
                })
                .then(({ ok, status, data }) => {
                    if (data.success) {
                        loadDashboardAttendanceStatus();
                        showNotification('Break ended!', 'success');
                    } else {
                        const message = data && data.message ? data.message : (status === 422 ? 'Workspace or user not resolved. Reselect a workspace and try again.' : 'Error ending break');
                        showNotification(message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error ending break', 'error');
                });
            }

            // Show notification function
            function showNotification(message, type) {
                // Simple notification - you can enhance this with toastr or other notification library
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
                        ${message}
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

            // Initialize dashboard attendance
            document.addEventListener('DOMContentLoaded', function() {
                loadDashboardAttendanceStatus();
                setInterval(loadDashboardAttendanceStatus, 30000); // Refresh every 30 seconds
            });
        </script>
        
        <style>
            .attendance-status-widget .status-circle {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.4rem;
                color: white;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            .attendance-status-widget .status-circle:hover {
                transform: scale(1.05);
            }

            .attendance-status-widget .status-indicator {
                display: flex;
                align-items: center;
            }

            .time-display {
                display: flex;
                align-items: center;
                padding: 1rem;
                background: #f8f9fa;
                border-radius: 10px;
                border: 1px solid #e9ecef;
                transition: all 0.3s ease;
                height: 100%;
            }

            .time-display:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .time-display.highlight {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
            }

            .time-display.highlight label {
                color: rgba(255, 255, 255, 0.9);
            }

            .time-display.highlight span {
                color: white;
            }

            .time-display .time-icon {
                width: 35px;
                height: 35px;
                background: rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 0.75rem;
                font-size: 1rem;
            }

            .time-display.highlight .time-icon {
                background: rgba(255, 255, 255, 0.2);
            }

            .time-display .time-content {
                flex: 1;
            }

            .time-display label {
                display: block;
                font-size: 0.7rem;
                color: #6c757d;
                margin-bottom: 0.25rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .time-display span {
                font-size: 1.1rem;
                font-weight: bold;
                color: #495057;
                display: block;
            }

            .attendance-actions .btn {
                border-radius: 8px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .attendance-actions .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            .attendance-actions .btn:active {
                transform: translateY(0);
            }

            .dashboard-card-modern {
                border: none;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
                border-radius: 12px;
                overflow: hidden;
            }

            .dashboard-card-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1.5rem;
                border: none;
            }

            .dashboard-card-header .card-title {
                color: white;
                font-weight: 600;
            }

            .dashboard-card-header .text-muted {
                color: rgba(255, 255, 255, 0.8) !important;
            }

            .dashboard-card-header .card-icon-wrapper {
                width: 45px;
                height: 45px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
            }

            .dashboard-card-body {
                padding: 1.5rem;
            }

            .dashboard-card-footer {
                background: #f8f9fa;
                border-top: 1px solid #e9ecef;
                padding: 1rem 1.5rem;
            }

            /* Mobile responsiveness improvements */
            @media (max-width: 992px) {
                .dashboard-card-header {
                    padding: 1.25rem;
                }
                .dashboard-card-body {
                    padding: 1.25rem;
                }
            }

            @media (max-width: 576px) {
                .attendance-status-widget .status-circle {
                    width: 40px;
                    height: 40px;
                    font-size: 1.1rem;
                }
                .attendance-status-widget .d-flex.align-items-center {
                    flex-wrap: wrap;
                }
                .attendance-status-widget .status-indicator {
                    margin-bottom: .5rem;
                }
                .time-display {
                    padding: .75rem;
                }
                .time-display .time-icon {
                    width: 28px;
                    height: 28px;
                    font-size: .9rem;
                }
                .time-display span {
                    font-size: 1rem;
                }
                .attendance-actions .btn {
                    font-size: .875rem;
                    padding: .5rem .75rem;
                }
                .dashboard-card-header .card-icon-wrapper {
                    width: 38px;
                    height: 38px;
                    font-size: 1.2rem;
                }
                .dashboard-card-footer {
                    padding: .75rem 1rem;
                }
                /* Ensure horizontal scrolling for any wide tables inside dashboard */
                .table-responsive {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }
            }
        </style>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="dashboard-card-footer">

                        <div class="d-flex justify-content-between align-items-center">

                            <small class="text-muted">Last updated: <span id="lastUpdated">--</span></small>

                            <div class="d-flex gap-2">

                                <a href="{{ route('attendance.breaks') }}" class="btn btn-outline-warning btn-sm">

                                    <i class="bx bx-coffee me-1"></i>Break Management

                                </a>

                                <a href="{{ route('attendance.tracker') }}" class="btn btn-outline-primary btn-sm">

                                    <i class="bx bx-time-five me-1"></i>Full Tracker

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        

        <script src="{{ asset('assets/js/apexcharts.js') }}"></script>

        <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>

        <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>

        

        <!-- Attendance Dashboard Scripts -->

        <script>

            let dashboardCurrentStatus = 'not_checked_in';



            // Load current attendance status for dashboard

            function loadDashboardAttendanceStatus() {

                fetch('{{ route("attendance.current-status") }}')

                    .then(response => response.json())

                    .then(data => {

                        dashboardCurrentStatus = data.status;

                        updateDashboardAttendanceUI(data);

                        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();

                    })

                    .catch(error => console.error('Error loading dashboard status:', error));

            }



            // Update dashboard attendance UI

            function updateDashboardAttendanceUI(data) {

                const statusCircle = document.getElementById('dashboardStatusCircle');

                const statusText = document.getElementById('dashboardStatusText');

                const statusSubtext = document.getElementById('dashboardStatusSubtext');

                const checkInTime = document.getElementById('dashboardCheckInTime');

                const workHours = document.getElementById('dashboardWorkHours');



                // Update status indicator

                switch(dashboardCurrentStatus) {

                    case 'not_checked_in':

                        statusCircle.className = 'status-circle bg-secondary';

                        statusText.textContent = 'Not Checked In';

                        statusSubtext.textContent = 'Click check-in to start your day';

                        break;

                    case 'checked_in':

                        statusCircle.className = 'status-circle bg-success';

                        statusText.textContent = 'Checked In';

                        statusSubtext.textContent = 'You are currently working';

                        break;

                    case 'on_break':

                        statusCircle.className = 'status-circle bg-warning';

                        statusText.textContent = 'On Break';

                        statusSubtext.textContent = 'You are currently on break';

                        break;

                    case 'checked_out':

                        statusCircle.className = 'status-circle bg-info';

                        statusText.textContent = 'Checked Out';

                        statusSubtext.textContent = 'You have completed your work day';

                        break;

                }



                // Update times

                if (data.attendance) {

                    checkInTime.textContent = data.attendance.check_in_time_formatted || '--:--';

                    workHours.textContent = data.attendance.total_work_hours_formatted || '00:00';

                }


                // Update buttons

                updateDashboardActionButtons();

            }



            // Update dashboard action buttons

            function updateDashboardActionButtons() {

                const checkInBtn = document.getElementById('dashboardCheckInBtn');

                const checkOutBtn = document.getElementById('dashboardCheckOutBtn');

                const startBreakBtn = document.getElementById('dashboardStartBreakBtn');

                const endBreakBtn = document.getElementById('dashboardEndBreakBtn');



                // Hide all buttons first

                checkInBtn.style.display = 'none';

                checkOutBtn.style.display = 'none';

                startBreakBtn.style.display = 'none';

                endBreakBtn.style.display = 'none';



                switch(dashboardCurrentStatus) {

                    case 'not_checked_in':

                        checkInBtn.style.display = 'block';

                        break;

                    case 'checked_in':

                        checkOutBtn.style.display = 'block';

                        startBreakBtn.style.display = 'block';

                        break;

                    case 'on_break':

                        endBreakBtn.style.display = 'block';

                        break;

                    case 'checked_out':

                        // No buttons for checked out status

                        break;

                }

            }



            // Dashboard attendance functions

            function dashboardCheckIn() {

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

                        loadDashboardAttendanceStatus();

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



            function dashboardCheckOut() {

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

                            loadDashboardAttendanceStatus();

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



            function dashboardStartBreak() {

                // Simple break start without modal for dashboard

                fetch('{{ route("attendance.start-break") }}', {

                    method: 'POST',

                    headers: {

                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

                        'Content-Type': 'application/json'

                    },

                    body: JSON.stringify({

                        break_type: 'other',

                        break_reason: 'Quick break'

                    })

                })

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        loadDashboardAttendanceStatus();

                        showNotification('Break started!', 'success');

                    } else {

                        showNotification(data.message, 'error');

                    }

                })

                .catch(error => {

                    console.error('Error:', error);

                    showNotification('Error starting break', 'error');

                });

            }



            function dashboardEndBreak() {

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

                        loadDashboardAttendanceStatus();

                        showNotification('Break ended!', 'success');

                    } else {

                        showNotification(data.message, 'error');

                    }

                })

                .catch(error => {

                    console.error('Error:', error);

                    showNotification('Error ending break', 'error');

                });

            }



            // Show notification function

            function showNotification(message, type) {

                // Simple notification - you can enhance this with toastr or other notification library

                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

                const alertHtml = `

                    <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">

                        ${message}

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



            // Initialize dashboard attendance

            document.addEventListener('DOMContentLoaded', function() {

                loadDashboardAttendanceStatus();

                setInterval(loadDashboardAttendanceStatus, 30000); // Refresh every 30 seconds

            });

        </script>

        

        <style>

            .attendance-status-widget .status-circle {

                width: 40px;
                height: 40px;
                border-radius: 50%;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 1.2rem;
                color: white;

            }



            .time-display {

                text-align: center;
                padding: 0.5rem;
                background: #f8f9fa;

                border-radius: 6px;
            }



            .time-display label {

                display: block;

                font-size: 0.75rem;
                color: #6c757d;

                margin-bottom: 0.25rem;

            }



            .time-display span {

                font-size: 1rem;
                font-weight: bold;

                color: #495057;

            }



            .attendance-actions .btn {

                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }

        </style>


@endsection



