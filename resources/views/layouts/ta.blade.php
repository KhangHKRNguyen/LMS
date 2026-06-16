@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand mb-3">
        ARENA
    </div>

    <div class="mt-4">
        <a href="{{ route('ta.dashboard') }}"
            class="sidebar-menu-item {{ Request::routeIs('ta.dashboard') ? 'active' : '' }}">
            TRANG CHỦ
        </a>

        <a href="{{ route('notifications.index') }}"
            class="sidebar-menu-item {{ Request::routeIs('notifications.*') ? 'active' : '' }}">
            THÔNG BÁO
        </a>
    </div>
</div>
@endsection

@section('main_content')
    @yield('ta_content')
@endsection
