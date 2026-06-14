@extends('layouts.lms')

@section('title', 'Tổng quan - Arena LMS')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm P-4">
                <h2 class="h4">Chào mừng bạn trở lại, {{ Auth::user()->name }}!</h2>
                <p class="text-muted">Bạn đã đăng nhập thành công vào hệ thống Arena LMS.</p>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection