<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Attendance Routes
|--------------------------------------------------------------------------
|
| Here is where you can register attendance routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::middleware(['web', 'auth', 'has_workspace'])->group(function () {
    
    // Main attendance routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    
    // API Routes for attendance tracking
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.start-break');
    Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.end-break');
    Route::get('/attendance/current-status', [AttendanceController::class, 'getCurrentStatus'])->name('attendance.current-status');
    Route::get('/attendance/weekly-summary', [AttendanceController::class, 'getWeeklySummary'])->name('attendance.weekly-summary');
    Route::post('/attendance/{attendance}/approve', [AttendanceController::class, 'approve'])->name('attendance.approve');
    Route::get('/attendance/statistics', [AttendanceController::class, 'getStatistics'])->name('attendance.statistics');
    Route::get('/attendance/reports', [AttendanceController::class, 'reports'])->name('attendance.reports');
    
    // View routes
    Route::get('/attendance/tracker', function() { 
        return view('attendance.tracker'); 
    })->name('attendance.tracker');
    
    Route::get('/attendance/breaks', function() { 
        return view('attendance.breaks'); 
    })->name('attendance.breaks');
    
    Route::get('/attendance/help', function() { 
        return view('attendance.help'); 
    })->name('attendance.help');
});





