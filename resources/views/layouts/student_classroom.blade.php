@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-brand mb-3">
    ARENA
</div>

{{-- Nút Quay lại Dashboard chính của Học viên --}}
<div class="px-3 mb-3">
    <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-danger w-100 text-start fw-bold shadow-sm py-2" style="border-radius: 6px;">
        <i class="bi bi-arrow-left-circle-fill me-2"></i> QUAY LẠI DASHBOARD
    </a>
</div>

<div class="mt-2">
    @if(isset($class))
        {{-- 1. DANH MỤC BÀI TẬP --}}
        <a href="{{ route('student.classes.show', $class->id) }}"
           class="sidebar-menu-item {{ Request::routeIs('student.classes.show') ? 'active' : '' }}">
            BÀI TẬP
        </a>

        {{-- 2. DANH MỤC TÀI LIỆU --}}
        <a href="{{ route('student.classes.materials', $class->id) }}"
           class="sidebar-menu-item {{ Request::routeIs('student.classes.materials') ? 'active' : '' }}">
            TÀI LIỆU
        </a>

        {{-- 3. KẾT QUẢ TỔNG KẾT --}}
        <a href="{{ route('student.classes.summary', $class->id) }}"
           class="sidebar-menu-item {{ Request::routeIs('student.classes.summary') ? 'active' : '' }}">
            KẾT QUẢ TỔNG KẾT
        </a>
    @endif
</div>
@endsection

@section('main_content')

        @yield('class_content')

@endsection