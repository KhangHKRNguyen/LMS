<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\CourseController;

use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\ClassWorkspaceController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\ClassroomSummaryController;
use App\Http\Controllers\Teacher\MaterialController;
use App\Http\Controllers\Teacher\ExamController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\SubmissionController;

use App\Http\Controllers\TA\TAClassController;
use App\Http\Controllers\TA\AttendanceController;
use App\Http\Controllers\TA\LeaveRequestController;

use App\Http\Controllers\Student\ClassController as StudentClassController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.accounts.index');
    } elseif ($user->role === 'teacher') {
        return redirect()->route('teacher.dashboard');
    } elseif ($user->role === 'ta') {
        return redirect()->route('ta.dashboard');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }

    abort(403, 'Tài khoản của bạn chưa được phân quyền truy cập.');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
});
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::patch('accounts/{user}/toggle-status', [AccountController::class, 'toggleStatus'])->name('accounts.toggleStatus');
    Route::get('accounts/sample', [AccountController::class, 'sample'])->name('accounts.sample');
    Route::post('accounts/preview', [AccountController::class, 'preview'])->name('accounts.preview');
    Route::post('accounts/store-bulk', [AccountController::class, 'storeBulk'])->name('accounts.store_bulk');
    Route::resource('accounts', AccountController::class);

    Route::resource('courses', CourseController::class);

    // Routes cho quản lý thành viên lớp
    Route::get('classes/{class}/members', [ClassController::class, 'members'])->name('classes.members');
    Route::post('classes/{class}/members/preview', [ClassController::class, 'previewMembers'])->name('classes.members.preview');
    Route::post('classes/{class}/members/store-bulk', [ClassController::class, 'storeBulkMembers'])->name('classes.members.store_bulk');
    Route::post('classes/{class}/members/add-single', [ClassController::class, 'addSingleMember'])->name('classes.members.add_single');
    Route::delete('classes/{classId}/members/{userId}', [ClassController::class, 'removeMember'])->name('classes.members.remove');
    Route::get('classes/{class}/search-users', [ClassController::class, 'searchUsers'])->name('classes.search_users');
    Route::get('classes/members/sample', [ClassController::class, 'downloadSample'])->name('classes.members.sample');
    
    Route::resource('classes', ClassController::class);
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    
    Route::get('/dashboard', [TeacherClassController::class, 'index'])->name('dashboard');

    Route::get('/assignments-history', [AssignmentController::class, 'globalIndex'])->name('assignments.global_index');

    Route::get('/classes/{class}', [ClassWorkspaceController::class, 'show'])->name('classes.show');

    Route::get('/classes/{class}/students', [ClassroomController::class, 'students'])->name('classroom.students');

    Route::get('/classes/{class}/summary', [ClassroomSummaryController::class, 'summary'])->name('classroom.summary');

    Route::resource('exams', ExamController::class);

    Route::get('/exams/{exam}/assign', [AssignmentController::class, 'assignView'])->name('assignments.assign');
    Route::post('/exams/{exam}/assign', [AssignmentController::class, 'assignStore'])->name('assignments.assign.store');

    Route::get('/classes/{class}/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/classes/{class}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/classes/{class}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');

    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::patch('/assignments/{assignment}/toggle-visibility', [AssignmentController::class, 'toggleVisibility'])->name('assignments.toggle-visibility');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    Route::get('/classes/{class}/assignments/{distribution}/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/classes/{class}/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::post('/classes/{class}/submissions/{submission}/post-grade', [SubmissionController::class, 'postGrade'])->name('submissions.post_grade');

    Route::get('/classes/{class}/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::post('/materials/store', [MaterialController::class, 'store'])->name('materials.store');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
});

Route::middleware(['auth', 'role:ta'])->prefix('ta')->name('ta.')->group(function () {
    Route::get('/dashboard', [TAClassController::class, 'index'])->name('dashboard');
    
    Route::get('/classes/{class}/attendance', [AttendanceController::class, 'classAttendance'])->name('classes.attendance');
    Route::post('/classes/{class}/attendance', [AttendanceController::class, 'storeMatrix'])->name('classes.attendance.store');
    
    Route::get('/classes/{class}/leave-requests', [LeaveRequestController::class, 'index'])->name('classes.leave_requests');
    Route::put('/leave-requests/{id}', [LeaveRequestController::class, 'update'])->name('leave_requests.update');

    Route::get('/classes/{class}/summary', function($id) { return "Giao diện kết quả tổng kết lớp của TA đang phát triển"; })->name('classes.summary');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentClassController::class, 'index'])->name('dashboard');
    
    Route::get('/leave-requests', function() { 
        return "Giao diện quản lý đơn của Học viên đang phát triển"; 
    })->name('leave_requests.index');

    Route::get('/classes/{class}', [StudentClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{class}/assignments/{distribution}', [StudentClassController::class, 'assignmentDetail'])->name('classes.assignments.detail');

    Route::get('/classes/{class}/assignments/{distribution}/take', [StudentClassController::class, 'takeAssignment'])->name('classes.assignments.take');
    Route::post('/classes/{class}/assignments/{distribution}/submit', [StudentClassController::class, 'submitAssignment'])->name('classes.assignments.submit');
    Route::get('/classes/{class}/assignments/{distribution}/submissions/{submission}', [StudentClassController::class, 'viewSubmission'])->name('classes.assignments.submissions.show');

    Route::get('/classes/{class}/materials', [StudentClassController::class, 'materials'])->name('classes.materials');
    Route::get('/classes/{class}/summary', [StudentClassController::class, 'summary'])->name('classes.summary');
});
require __DIR__.'/auth.php';
