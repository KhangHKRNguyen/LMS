@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand">
        ARENA
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.dashboard') }}"
            class="sidebar-menu-item {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
            QUẢN LÝ LỚP HỌC
        </a>

        <a href="{{ route('admin.accounts.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('admin.accounts.*') ? 'active' : '' }}">
            QUẢN LÝ TÀI KHOẢN
        </a>
    </div>
</div>
@endsection

@section('main_content')
    @yield('admin_content')
@endsection