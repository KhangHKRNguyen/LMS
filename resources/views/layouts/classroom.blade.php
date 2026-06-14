@extends('layouts.sidebar')

@section('sidebar')

<div class="sidebar-brand mb-3">
    ARENA
</div>

{{-- Nút Quay lại Dashboard của Teacher --}}
<div class="px-3 mb-3">
    <a href="{{ route('teacher.dashboard') }}" class="btn btn-sm btn-outline-danger w-100 text-start fw-bold shadow-sm py-2" style="border-radius: 6px;">
        <i class="bi bi-arrow-left-circle-fill me-2"></i> QUAY LẠI DASHBOARD
    </a>
</div>

<div class="mt-2">
    @if(isset($class))
        {{-- 1. THÔNG TIN CHUNG (Thư mục: teacher/classes/info.blade.php) --}}
        <a href="{{ route('teacher.classes.show', $class->id) }}"
           class="sidebar-menu-item {{ Request::routeIs('teacher.classes.show') ? 'active' : '' }}">
            THÔNG TIN CHUNG
        </a>

        {{-- 2. TÀI LIỆU (Thư mục: teacher/materials/document.blade.php) --}}
        <a href="{{ route('teacher.materials.index', ['class_id' => $class->id]) }}"
           class="sidebar-menu-item {{ Request::routeIs('teacher.materials.*') ? 'active' : '' }}">
            TÀI LIỆU
        </a>

        {{-- 3. DANH SÁCH HỌC VIÊN (Thư mục: teacher/students/index.blade.php) --}}
        <a href="{{ route('teacher.classroom.students', $class->id) }}"
           class="sidebar-menu-item {{ Request::routeIs('teacher.classroom.students') ? 'active' : '' }}">
            DANH SÁCH HỌC VIÊN
        </a>

        {{-- 4. GIAO BÀI (Thư mục: teacher/assignments/create.blade.php) --}}
        <a href="{{ route('teacher.assignments.create', $class->id) }}"
           class="sidebar-menu-item {{ Request::routeIs('teacher.assignments.create') ? 'active' : '' }}">
            GIAO BÀI
        </a>

        {{-- 5. BÀI NỘP / CHẤM ĐIỂM (Thư mục: teacher/submissions/index.blade.php) 
             Lưu ý: Bạn cần kiểm tra chính xác tên route hiển thị danh sách bài nộp của bạn là gì (ví dụ: teacher.submissions.index hoặc teacher.grades.index) --}}
        <a href="{{ route('teacher.assignments.index', ['class_id' => $class->id]) }}"
           class="sidebar-menu-item {{ (Request::routeIs('teacher.assignments.index') || Request::routeIs('teacher.submissions.*')) ? 'active' : '' }}">
            BÀI NỘP
        </a>

        {{-- 6. KẾT QUẢ TỔNG KẾT (Thư mục: teacher/summary/index.blade.php) --}}
        <a href="{{ route('teacher.classroom.summary', $class->id) }}"
           class="sidebar-menu-item {{ Request::routeIs('teacher.classroom.summary') ? 'active' : '' }}">
            KẾT QUẢ TỔNG KẾT
        </a>
    @endif
</div>

@endsection

@section('main_content')
    @yield('classroom_content')
@endsection