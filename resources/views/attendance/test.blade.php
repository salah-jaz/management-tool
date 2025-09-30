@extends('layout')
@section('title')
    Attendance Test
@endsection
@section('content')
    @authBoth
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Attendance System Test</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Current Status</h6>
                                <div id="currentStatus">Loading...</div>
                                <div id="attendanceInfo"></div>
                            </div>
                            <div class="col-md-6">
                                <h6>Actions</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success" onclick="testCheckIn()">Test Check In</button>
                                    <button class="btn btn-danger" onclick="testCheckOut()">Test Check Out</button>
                                    <button class="btn btn-warning" onclick="testStartBreak()">Test Start Break</button>
                                    <button class="btn btn-info" onclick="testEndBreak()">Test End Break</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>API Responses</h6>
                                <div id="apiResponses" class="bg-light p-3" style="height: 200px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logResponse(action, response) {
            const responses = document.getElementById('apiResponses');
            const timestamp = new Date().toLocaleTimeString();
            responses.innerHTML += `<div><strong>${timestamp} - ${action}:</strong> ${JSON.stringify(response, null, 2)}</div>`;
            responses.scrollTop = responses.scrollHeight;
        }

        function loadCurrentStatus() {
            fetch('{{ route("attendance.current-status") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('currentStatus').textContent = data.status;
                    document.getElementById('attendanceInfo').innerHTML = `
                        <div>Attendance ID: ${data.attendance ? data.attendance.id : 'None'}</div>
                        <div>Check In: ${data.attendance ? data.attendance.check_in_time : 'None'}</div>
                        <div>Check Out: ${data.attendance ? data.attendance.check_out_time : 'None'}</div>
                        <div>Work Hours: ${data.attendance ? data.attendance.total_work_hours : 'None'}</div>
                    `;
                    logResponse('Current Status', data);
                })
                .catch(error => {
                    logResponse('Current Status Error', error.message);
                });
        }

        function testCheckIn() {
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
                logResponse('Check In', data);
                loadCurrentStatus();
            })
            .catch(error => {
                logResponse('Check In Error', error.message);
            });
        }

        function testCheckOut() {
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
                logResponse('Check Out', data);
                loadCurrentStatus();
            })
            .catch(error => {
                logResponse('Check Out Error', error.message);
            });
        }

        function testStartBreak() {
            fetch('{{ route("attendance.start-break") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    break_type: 'other',
                    break_reason: 'Test break'
                })
            })
            .then(response => response.json())
            .then(data => {
                logResponse('Start Break', data);
                loadCurrentStatus();
            })
            .catch(error => {
                logResponse('Start Break Error', error.message);
            });
        }

        function testEndBreak() {
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
                logResponse('End Break', data);
                loadCurrentStatus();
            })
            .catch(error => {
                logResponse('End Break Error', error.message);
            });
        }

        // Load initial status
        document.addEventListener('DOMContentLoaded', function() {
            loadCurrentStatus();
        });
    </script>
    @endauthBoth
@endsection

