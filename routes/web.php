<?php

use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Student\LeaveRequestController;
use App\Http\Controllers\Student\StudyController;
use App\Http\Controllers\Student\DoAssignmentController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\Student\FeedbackController;
use App\Http\Controllers\Student\DashboardController;

use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\GradeController;
use App\Http\Controllers\Teacher\MaterialController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\ClassWorkspaceController;
use App\Http\Controllers\Teacher\ClassroomSummaryController;

use App\Http\Controllers\TA\TAClassController;
use App\Http\Controllers\TA\LeaveRequestController as TALeaveRequestController;
use App\Http\Controllers\TA\AttendanceController as TAAttendanceController;
use App\Http\Controllers\TA\ClassroomSummaryController as TAClassroomSummaryController;

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;

use Illuminate\Support\Facades\Route;

// ==========================================
// ĐIỀU HƯỚNG SƠ KHỞI THEO VAI TRÒ (ROLE)
// ==========================================

Route::get('/', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'admin'   => redirect()->route('admin.accounts.index'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.results.index'),
            'ta'      => redirect()->route('ta.classes.index'),
            default   => redirect('/dashboard'),
        };
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'admin'   => redirect()->route('admin.accounts.index'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.results.index'),
            'ta'      => redirect()->route('ta.classes.index'),
            default   => view('dashboard'),
        };
    }
    return redirect()->route('login');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================================
// NHÓM TRỢ GIẢNG (TA)
// ==========================================
Route::middleware(['auth', 'role:ta'])->prefix('ta')->name('ta.')->group(function () {
    
    // Quản lý Lớp học của TA (Lấy trang này làm trang chủ thay dashboard)
    Route::get('/classes', [TAClassController::class, 'index'])->name('classes.index');
    
    // Điểm danh ma trận dựa trên ID lớp học
    Route::get('/classes/{id}/attendance', [TAAttendanceController::class, 'classAttendance'])->name('classes.attendance');
    Route::post('/classes/{id}/attendance/save', [TAAttendanceController::class, 'storeMatrix'])->name('classes.attendance.store');

    // Điểm danh cũ / thông thường
    Route::get('/attendance', [TAAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [TAAttendanceController::class, 'store'])->name('attendance.store');
    
    // Duyệt đơn xin nghỉ học
    Route::get('/leave-requests', [TALeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::put('/leave-requests/{id}', [TALeaveRequestController::class, 'update'])->name('leave_requests.update');
    
    // Xem tổng kết lớp học
    Route::get('classroom/{class_id}/summary', [TAClassroomSummaryController::class, 'summary'])->name('classroom.summary');
});

// ==========================================
// NHÓM HỌC SINH (STUDENT)
// ==========================================
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Xem kết quả và Phản hồi
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{id}', [ResultController::class, 'show'])->name('results.show');
    
    // Nếu muốn dùng FeedbackController, bạn có thể đổi hàm ở đây, hiện tại giữ nguyên logic của bạn:
    Route::post('/feedback', [ResultController::class, 'storeFeedback'])->name('feedback.store');
    Route::post('/results/{id}/feedback', [ResultController::class, 'storeFeedback'])->name('results.feedback');

    // Đơn xin nghỉ bám theo Buổi Học
    Route::prefix('leave-requests')->name('leave_requests.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
    });

    // Xem file bài nộp trực tuyến
    Route::get('/submissions/{submission}/view', [DoAssignmentController::class, 'viewFile'])->name('submissions.view');

    // Tài liệu học tập
    Route::prefix('study')->name('study.')->group(function () {
        Route::get('/', [StudyController::class, 'index'])->name('index');
        Route::get('/download/{id}', [StudyController::class, 'downloadMaterial'])->name('download');
    });

    // Làm bài tập
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [DoAssignmentController::class, 'index'])->name('index');
        Route::get('/{id}', [DoAssignmentController::class, 'show'])->name('show');
        Route::post('/{id}', [DoAssignmentController::class, 'store'])->name('store');
    });
});

// ==========================================
// NHÓM QUẢN TRỊ (ADMIN)
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminClassController::class, 'index'])->name('dashboard');

    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/sample', [AccountController::class, 'sample'])->name('sample');
        Route::post('/preview', [AccountController::class, 'preview'])->name('preview');
        Route::post('/store-bulk', [AccountController::class, 'store_bulk'])->name('store_bulk');
        Route::patch('/{user}/toggle-status', [AccountController::class, 'toggleStatus'])->name('toggleStatus');
    });
    Route::resource('accounts', AccountController::class);

    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/{class}/members', [AdminClassController::class, 'members'])->name('members');
        Route::get('/{class}/members/sample', [AdminClassController::class, 'sampleMembers'])->name('members.sample');
        Route::post('/{class}/members/preview', [AdminClassController::class, 'previewMembers'])->name('members.preview');
        Route::post('/{class}/members/store-bulk', [AdminClassController::class, 'storeMembersBulk'])->name('members.store_bulk');
        
        Route::post('/{class}/members/add-single', [AdminClassController::class, 'addMemberSingle'])->name('members.add_single');
        Route::post('/{class}/assign-teacher', [AdminClassController::class, 'assignTeacher'])->name('assign-teacher');
        Route::post('/{class}/add-students', [AdminClassController::class, 'addStudents'])->name('add-students');
        
        Route::delete('/{class}/members/{user}/remove', [AdminClassController::class, 'removeMember'])->name('members.remove');
    });
    Route::resource('classes', AdminClassController::class);
});

// ==========================================
// NHÓM GIẢNG VIÊN (TEACHER)
// ==========================================
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [ClassController::class, 'index'])->name('dashboard');

    Route::get('/classes/{id}', [ClassWorkspaceController::class, 'show'])->name('classes.show');
    Route::get('classroom/{class_id}/students', [ClassroomController::class, 'students'])->name('classroom.students');

    Route::get('materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::delete('materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    Route::get('classroom/{class_id}/summary', [ClassroomSummaryController::class, 'summary'])->name('classroom.summary');

    // Quản lý danh sách bài tập theo lớp
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    
    // Giao bài tập cho một lớp cụ thể
    Route::get('/classes/{courseClass}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    
    // Chi tiết bài tập và tính năng phụ trợ
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::patch('/assignments/{assignment}/toggle-visibility', [AssignmentController::class, 'toggleVisibility'])->name('assignments.toggle-visibility');
    Route::get('/assignments/{assignment}/export', [AssignmentController::class, 'export'])->name('assignments.export');
    Route::get('/assignments/{assignment}/download', [AssignmentController::class, 'downloadAttachment'])->name('assignments.download');
    Route::get('/assignments/template', [AssignmentController::class, 'template'])->name('assignments.template');
});

Route::get('/captcha-image', function () {
    // 1. Sinh chuỗi ký tự ngẫu nhiên
    $charset = 'aeiouDT';
    $length = 7;
    $captchaString = '';
    for ($i = 0; $i < $length; $i++) {
        $captchaString .= $charset[random_int(0, strlen($charset) - 1)];
    }

    session(['captcha_code' => $captchaString]);

    // 2. Tạo kích thước khung ảnh
    $width = 220;
    $height = 70;
    $image = imagecreatetruecolor($width, $height);

    // 3. Cấu hình màu sắc cơ bản
    $background = imagecolorallocate($image, 245, 245, 245);
    $border = imagecolorallocate($image, 200, 200, 200);
    $colors = [
        imagecolorallocate($image, 35, 65, 120),
        imagecolorallocate($image, 80, 120, 50),
        imagecolorallocate($image, 150, 50, 100),
        imagecolorallocate($image, 90, 40, 120),
    ];

    imagefilledrectangle($image, 0, 0, $width, $height, $background);
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $border);

    // 4. Vẽ các đường thẳng nhiễu chống bot
    for ($i = 0; $i < 50; $i++) {
        $noiseColor = imagecolorallocate($image, random_int(150, 220), random_int(150, 220), random_int(150, 220));
        imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $noiseColor);
    }

    // 5. Vẽ chữ lên hình
    $charSpace = (int)($width / $length);
    for ($i = 0; $i < strlen($captchaString); $i++) {
        $char = $captchaString[$i];
        $x = 16 + $i * $charSpace;
        $color = $colors[array_rand($colors)];
        imagestring($image, 5, $x, (int)($height / 4) + random_int(-5, 5), $char, $color);
    }

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return response($imageData)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
});

require __DIR__.'/auth.php';