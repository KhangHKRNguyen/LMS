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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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
    
    Route::get('/dashboard', [\App\Http\Controllers\Teacher\ClassController::class, 'index'])->name('dashboard');

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

    Route::get('/classes/{class}/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::post('/materials/store', [MaterialController::class, 'store'])->name('materials.store');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
});

require __DIR__.'/auth.php';
