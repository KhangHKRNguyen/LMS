@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('student.dashboard') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách lớp học
    </a>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-radius: 8px;">
    <div class="card-body p-4 bg-white" style="border-top: 4px solid var(--primary-color); border-radius: 8px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-danger-subtle text-danger fw-bold mb-2 px-2 py-1 fs-8">KHÔNG GIAN HỌC VIÊN</span>
                <h4 class="fw-bold text-dark mb-1">Lớp: U206 - Lập trình Web nâng cao với PHP & Laravel</h4>
                <p class="text-muted m-0 fs-7">Giảng viên: <strong>ThS. Nguyễn Văn A</strong> &nbsp;|&nbsp; Phòng học: <strong>Lab 402</strong></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm p-2" style="border-radius: 8px; background-color: #white;">
            <div class="px-3 py-2 fw-bold text-secondary border-bottom mb-2 fs-8 text-uppercase">Tính năng lớp học</div>
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                
                <a href="#" class="nav-link py-2.5 px-3 mb-1 d-flex align-items-center gap-2 fw-semibold @yield('active_assignments')" style="border-radius: 6px; font-size: 14px;">
                    <i class="bi bi-file-earmark-text-fill"></i> 1. Danh sách bài tập
                </a>
                
                <a href="#" class="nav-link py-2.5 px-3 mb-1 d-flex align-items-center gap-2 fw-semibold @yield('active_materials')" style="border-radius: 6px; font-size: 14px;">
                    <i class="bi bi-folder-fill"></i> 2. Tài liệu học tập
                </a>
                
                <a href="#" class="nav-link py-2.5 px-3 d-flex align-items-center gap-2 fw-semibold @yield('active_grades')" style="border-radius: 6px; font-size: 14px;">
                    <i class="bi bi-trophy-fill"></i> 3. Kết quả học tập môn
                </a>
                
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        @yield('class_content')
    </div>
</div>

<style>
    /* CSS bổ trợ để active menu sidebar chuẩn màu hệ thống */
    #v-pills-tab .nav-link {
        color: #64748B;
    }
    #v-pills-tab .nav-link:hover {
        background-color: #F1F5F9;
        color: var(--primary-color);
    }
    #v-pills-tab .nav-link.active-custom {
        background-color: var(--primary-color) !important;
        color: white !important;
    }
</style>
@endsection