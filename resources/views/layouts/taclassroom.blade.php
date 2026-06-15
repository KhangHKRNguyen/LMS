@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand mb-3">
        ARENA
    </div>

    {{-- Nút Quay lại Dashboard chính --}}
    <div class="px-3 mb-3">
        <a href="{{ route('ta.dashboard') }}" class="btn btn-sm btn-outline-danger w-100 text-start fw-bold shadow-sm py-2" style="border-radius: 6px;">
            <i class="bi bi-arrow-left-circle-fill me-2"></i> QUAY LẠI DASHBOARD
        </a>
    </div>

    {{-- Danh sách 3 tính năng duy nhất của TA --}}
    <div class="mt-2">
        @if(isset($class))
            {{-- 1. ĐIỂM DANH --}}
            <a href="{{ route('ta.classes.attendance', $class->id) }}"
               class="sidebar-menu-item {{ Request::routeIs('ta.classes.attendance') ? 'active' : '' }}">
                ĐIỂM DANH
            </a>

            {{-- 2. ĐƠN XIN NGHỈ --}}
            <a href="{{ route('ta.classes.leave_requests', $class->id) }}"
               class="sidebar-menu-item {{ Request::routeIs('ta.classes.leave_requests') ? 'active' : '' }}">
                ĐƠN XIN NGHỈ
            </a>

            {{-- 3. KẾT QUẢ TỔNG KẾT --}}
            <a href="{{ route('ta.classes.summary', $class->id) }}"
               class="sidebar-menu-item {{ Request::routeIs('ta.classes.summary') ? 'active' : '' }}">
                KẾT QUẢ TỔNG KẾT
            </a>
        @endif
    </div>
</div>
@endsection

@section('main_content')
    @yield('taclassroom_content')
@endsection