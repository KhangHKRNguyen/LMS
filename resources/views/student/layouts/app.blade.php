<div class="nav flex-column navigation-sidebar">
    <a href="{{ route('student.dashboard') }}" class="nav-link py-2.5 px-3 mb-1 d-flex align-items-center gap-2 fw-semibold {{ Request::routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2-fill"></i> Trang chủ
    </a>

    <a href="{{ route('student.applications.index') }}" class="nav-link py-2.5 px-3 mb-1 d-flex align-items-center gap-2 fw-semibold {{ Request::routeIs('student.applications.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-person-fill"></i> Quản lý đơn
        <span class="badge bg-warning text-dark ms-auto fw-bold fs-9 rounded-pill">1</span> </a>
    
    </div>