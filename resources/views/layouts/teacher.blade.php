@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand">
        ARENA
    </div>

    <div class="mt-4">
        <a href="{{ route('teacher.dashboard') }}"
            class="sidebar-menu-item {{ Request::routeIs('teacher.dashboard') ? 'active' : '' }}">
            TRANG CHỦ
        </a>

        <a href="{{ route('teacher.exams.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('teacher.exams.*') ? 'active' : '' }}">
            NGÂN HÀNG ĐỀ
        </a>
        <a href="{{ route('notifications.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('notifications.*') ? 'active' : '' }}">
            THÔNG BÁO
        </a>
    </div>
</div>
@endsection

@section('main_content')
    @yield('teacher_content')
@endsection
