@extends('layouts.lms')

@section('content')

<div class="sidebar-arena">
    @yield('sidebar')
</div>

<div class="main-content">

    <div class="d-flex justify-content-end align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">

            <div class="position-relative">
                <i class="bi bi-bell-fill fs-4 text-secondary"></i>
            </div>

            <div class="dropdown">
                <div
                    class="d-flex align-items-center gap-2"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="cursor: pointer;"
                >
                    <img
                        src="https://ui-avatars.com/api/?name=Admin&background=990000&color=fff"
                        alt="Avatar"
                        class="rounded-circle"
                        style="width: 38px; height: 38px; border: 2px solid var(--primary-color);"
                    >

                    <div class="text-end">
                        <div class="fw-bold" style="font-size:14px;">
                            Nguyễn Văn A
                        </div>
                        <div class="text-muted" style="font-size:12px;">
                            Ban đào tạo
                        </div>
                    </div>

                    <i class="bi bi-chevron-down text-secondary"></i>
                </div>

                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                    <li>
                        <a
                            class="dropdown-menu-item d-flex align-items-center gap-2 px-3 py-2 text-dark text-decoration-none"
                            href="{{ route('profile.edit') }}"
                        >
                            <i class="bi bi-person"></i>
                            Hồ sơ
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="dropdown-menu-item d-flex align-items-center gap-2 px-3 py-2 text-danger border-0 bg-transparent w-100 text-start"
                            >
                                <i class="bi bi-box-arrow-right"></i>
                                Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4 alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            @if(session('error'))
                {{ session('error') }}
            @else
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @yield('main_content')

</div>

@endsection