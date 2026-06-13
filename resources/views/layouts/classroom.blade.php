@extends('layouts.sidebar')

@section('sidebar')

<div class="sidebar-brand">
    ARENA
</div>

<div class="mt-4">

    <a href="{{ route('teacher.classes.show', $class->id) }}"
    class="sidebar-menu-item">
        THÔNG TIN CHUNG
    </a>

    <a href="{{ route('teacher.materials.index') }}"
    class="sidebar-menu-item">
        TÀI LIỆU
    </a>

    <a href="{{ route('teacher.classroom.students', $class->id) }}"
    class="sidebar-menu-item">
        DANH SÁCH HỌC VIÊN
    </a>

    <a href="{{ route('teacher.assignments.create', $class->id) }}"
    class="sidebar-menu-item">
        GIAO BÀI
    </a>

    <a href="{{ route('teacher.assignments.index') }}"
    class="sidebar-menu-item">
        BÀI NỘP
    </a>

    <a href="{{ route('teacher.classroom.summary', $class->id) }}"
    class="sidebar-menu-item">
        KẾT QUẢ TỔNG KẾT
    </a>

</div>

@endsection

@section('main_content')
    @yield('classroom_content')
@endsection