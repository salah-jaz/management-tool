@extends('layout')
@section('title')
    <?= get_label('break_help', 'Break Management Help') ?>
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-1">
                            <i class="bx bx-help-circle me-2"></i>
                            How to Add and Manage Breaks
                        </h4>
                        <p class="text-muted mb-0">Complete guide to using the break management system</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Start Guide -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-rocket me-2"></i>
                            Quick Start Guide
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="step-card">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        <h6>Check In First</h6>
                                        <p>You must be checked in to start a break. Use the "Check In" button on the dashboard.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="step-card">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                        <h6>Start Your Break</h6>
                                        <p>Click "Start Break" button or use quick break actions for common break types.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="step-card">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        <h6>End Your Break</h6>
                                        <p>When you return, click "End Break" to stop the break timer.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="step-card">
                                    <div class="step-number">4</div>
                                    <div class="step-content">
                                        <h6>Check Out</h6>
                                        <p>At the end of your workday, click "Check Out" to complete your attendance.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Methods to Add Breaks -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-ul me-2"></i>
                            Methods to Add Breaks
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Method 1: Dashboard Widget -->
                            <div class="col-md-4 mb-4">
                                <div class="method-card">
                                    <div class="method-icon">
                                        <i class="bx bx-home"></i>
                                    </div>
                                    <h6>Dashboard Widget</h6>
                                    <p>Quick break management directly from your dashboard</p>
                                    <ul class="method-features">
                                        <li>One-click break start/end</li>
                                        <li>Real-time status updates</li>
                                        <li>Work hours tracking</li>
                                    </ul>
                                    <a href="{{ url('home') }}" class="btn btn-primary btn-sm">Go to Dashboard</a>
                                </div>
                            </div>

                            <!-- Method 2: Break Management Page -->
                            <div class="col-md-4 mb-4">
                                <div class="method-card">
                                    <div class="method-icon">
                                        <i class="bx bx-coffee"></i>
                                    </div>
                                    <h6>Break Management</h6>
                                    <p>Dedicated page for comprehensive break management</p>
                                    <ul class="method-features">
                                        <li>Quick break type buttons</li>
                                        <li>Custom break options</li>
                                        <li>Break history and summary</li>
                                        <li>Timer display</li>
                                    </ul>
                                    <a href="{{ route('attendance.breaks') }}" class="btn btn-warning btn-sm">Open Break Management</a>
                                </div>
                            </div>

                            <!-- Method 3: Full Tracker -->
                            <div class="col-md-4 mb-4">
                                <div class="method-card">
                                    <div class="method-icon">
                                        <i class="bx bx-time-five"></i>
                                    </div>
                                    <h6>Full Attendance Tracker</h6>
                                    <p>Complete attendance tracking with detailed controls</p>
                                    <ul class="method-features">
                                        <li>Detailed break management</li>
                                        <li>Location tracking</li>
                                        <li>Photo capture</li>
                                        <li>Advanced settings</li>
                                    </ul>
                                    <a href="{{ route('attendance.tracker') }}" class="btn btn-info btn-sm">Open Full Tracker</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Break Types -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-category me-2"></i>
                            Break Types Available
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <div class="break-type-card">
                                    <div class="break-type-icon bg-primary">
                                        <i class="bx bx-restaurant"></i>
                                    </div>
                                    <h6>Lunch</h6>
                                    <p>Meal breaks</p>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="break-type-card">
                                    <div class="break-type-icon bg-info">
                                        <i class="bx bx-coffee"></i>
                                    </div>
                                    <h6>Coffee</h6>
                                    <p>Short refreshment breaks</p>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="break-type-card">
                                    <div class="break-type-icon bg-warning">
                                        <i class="bx bx-user"></i>
                                    </div>
                                    <h6>Personal</h6>
                                    <p>Personal time</p>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="break-type-card">
                                    <div class="break-type-icon bg-success">
                                        <i class="bx bx-group"></i>
                                    </div>
                                    <h6>Meeting</h6>
                                    <p>Work-related meetings</p>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="break-type-card">
                                    <div class="break-type-icon bg-secondary">
                                        <i class="bx bx-dots-horizontal"></i>
                                    </div>
                                    <h6>Other</h6>
                                    <p>Miscellaneous breaks</p>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="break-type-card">
                                    <div class="break-type-icon bg-danger">
                                        <i class="bx bx-plus"></i>
                                    </div>
                                    <h6>Custom</h6>
                                    <p>Custom break with details</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step-by-Step Instructions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-check me-2"></i>
                            Step-by-Step Instructions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="instructionsAccordion">
                            <!-- Dashboard Method -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardMethod">
                                        <i class="bx bx-home me-2"></i>
                                        Method 1: Using Dashboard Widget
                                    </button>
                                </h2>
                                <div id="dashboardMethod" class="accordion-collapse collapse show" data-bs-parent="#instructionsAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li><strong>Check In:</strong> Click the "Check In" button on your dashboard</li>
                                            <li><strong>Start Break:</strong> Once checked in, click "Start Break" button</li>
                                            <li><strong>End Break:</strong> When you return, click "End Break" button</li>
                                            <li><strong>Check Out:</strong> At the end of your day, click "Check Out"</li>
                                        </ol>
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            The dashboard widget shows your current status and work hours in real-time.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Break Management Method -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#breakManagementMethod">
                                        <i class="bx bx-coffee me-2"></i>
                                        Method 2: Using Break Management Page
                                    </button>
                                </h2>
                                <div id="breakManagementMethod" class="accordion-collapse collapse" data-bs-parent="#instructionsAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li><strong>Navigate:</strong> Go to Attendance → Break Management</li>
                                            <li><strong>Quick Breaks:</strong> Click on any break type button (Lunch, Coffee, Personal, etc.)</li>
                                            <li><strong>Custom Break:</strong> Click "Custom" for detailed break options</li>
                                            <li><strong>Monitor:</strong> Watch the timer and break summary</li>
                                            <li><strong>End Break:</strong> Click "End Break" when you return</li>
                                        </ol>
                                        <div class="alert alert-warning">
                                            <i class="bx bx-coffee me-2"></i>
                                            This method provides more control and detailed break tracking.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Full Tracker Method -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fullTrackerMethod">
                                        <i class="bx bx-time-five me-2"></i>
                                        Method 3: Using Full Attendance Tracker
                                    </button>
                                </h2>
                                <div id="fullTrackerMethod" class="accordion-collapse collapse" data-bs-parent="#instructionsAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li><strong>Navigate:</strong> Go to Attendance → Attendance Tracker</li>
                                            <li><strong>Check In:</strong> Click "Check In" if not already checked in</li>
                                            <li><strong>Start Break:</strong> Click "Start Break" and select break type</li>
                                            <li><strong>Add Details:</strong> Optionally add reason and expected duration</li>
                                            <li><strong>End Break:</strong> Click "End Break" when returning</li>
                                            <li><strong>View History:</strong> See all your breaks for the day</li>
                                        </ol>
                                        <div class="alert alert-success">
                                            <i class="bx bx-time-five me-2"></i>
                                            This method offers the most comprehensive attendance tracking features.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips and Best Practices -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-bulb me-2"></i>
                            Tips and Best Practices
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bx bx-check-circle text-success me-2"></i>Do's</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bx bx-check text-success me-2"></i>Always check in before taking breaks</li>
                                    <li><i class="bx bx-check text-success me-2"></i>Use appropriate break types</li>
                                    <li><i class="bx bx-check text-success me-2"></i>End breaks when you return</li>
                                    <li><i class="bx bx-check text-success me-2"></i>Add reasons for longer breaks</li>
                                    <li><i class="bx bx-check text-success me-2"></i>Check out at the end of your day</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bx bx-x-circle text-danger me-2"></i>Don'ts</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bx bx-x text-danger me-2"></i>Don't forget to end breaks</li>
                                    <li><i class="bx bx-x text-danger me-2"></i>Don't take breaks without checking in</li>
                                    <li><i class="bx bx-x text-danger me-2"></i>Don't leave breaks running overnight</li>
                                    <li><i class="bx bx-x text-danger me-2"></i>Don't abuse break time</li>
                                    <li><i class="bx bx-x text-danger me-2"></i>Don't forget to check out</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-zap me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ url('home') }}" class="btn btn-outline-primary w-100">
                                    <i class="bx bx-home d-block mb-2"></i>
                                    Dashboard
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('attendance.breaks') }}" class="btn btn-outline-warning w-100">
                                    <i class="bx bx-coffee d-block mb-2"></i>
                                    Break Management
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('attendance.tracker') }}" class="btn btn-outline-info w-100">
                                    <i class="bx bx-time-five d-block mb-2"></i>
                                    Full Tracker
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('attendance.index') }}" class="btn btn-outline-success w-100">
                                    <i class="bx bx-list-ul d-block mb-2"></i>
                                    Attendance Records
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .step-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .step-content h6 {
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .step-content p {
            margin-bottom: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .method-card {
            text-align: center;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            height: 100%;
            transition: all 0.3s ease;
        }

        .method-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .method-icon {
            width: 60px;
            height: 60px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .method-features {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .method-features li {
            padding: 0.25rem 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .method-features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .break-type-card {
            text-align: center;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            height: 100%;
        }

        .break-type-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin: 0 auto 0.5rem;
        }

        .break-type-card h6 {
            margin-bottom: 0.25rem;
            color: #495057;
        }

        .break-type-card p {
            margin-bottom: 0;
            color: #6c757d;
            font-size: 0.8rem;
        }

        .accordion-button {
            font-weight: 600;
        }

        .accordion-button:not(.collapsed) {
            background-color: #e7f3ff;
            border-color: #b3d9ff;
        }

        .alert {
            border-left: 4px solid;
        }

        .alert-info {
            border-left-color: #17a2b8;
        }

        .alert-warning {
            border-left-color: #ffc107;
        }

        .alert-success {
            border-left-color: #28a745;
        }
    </style>
    @endpush
    @endauthBoth
@endsection

