@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand">
        ARENA
    </div>

    <div class="mt-4">
        <a href="{{ route('student.dashboard') }}"
            class="sidebar-menu-item {{ Request::routeIs('student.dashboard') ? 'active' : '' }}">
            DANH SÁCH LỚP HỌC
        </a>

        <a href="{{ route('student.leave_requests.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('student.leave_requests.*') ? 'active' : '' }}">
            QUẢN LÝ ĐƠN
        </a>
    </div>
</div>
@endsection

@section('main_content')
    @yield('student_content')
@endsection