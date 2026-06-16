@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand">
        ARENA
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.courses.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('admin.courses.*') || Request::routeIs('admin.classes.*') ? 'active' : '' }}">
            QUẢN LÝ KHÓA HỌC
        </a>

        <a href="{{ route('admin.accounts.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('admin.accounts.*') ? 'active' : '' }}">
            QUẢN LÝ TÀI KHOẢN
        </a>
        <a href="{{ route('notifications.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('notifications.*') ? 'active' : '' }}">
            THÔNG BÁO
        </a>
    </div>
</div>
@endsection

@section('main_content')
    @yield('admin_content')
@endsection
