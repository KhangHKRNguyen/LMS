@extends('layouts.sidebar')

@section('sidebar')
<div class="sidebar-arena">
    <div class="sidebar-brand mb-3">
        ARENA
    </div>
</div>
@endsection

@section('main_content')
    @yield('ta_content')
@endsection