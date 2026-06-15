@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand">
        ARENA
    </div>

    <div class="mt-4">
        <a href="{{ route('dashboard') }}"
            class="sidebar-menu-item">
            QUAY LẠI
        </a>
    </div>
</div>
@endsection

@section('main_content')
    @yield('profile_content')
@endsection
