@extends('layouts.ta')

@section('title', 'Trợ lý - Trang chủ')

@section('ta_content')
<div class="text-center mb-5">
    <h3 class="fw-bold" style="letter-spacing: 1px; color: #990000;">BẢNG ĐIỀU KHIỂN TRỢ LÝ GIẢNG DẠY (TA)</h3>
    <p class="text-muted fw-medium">Học kỳ II - Năm học 2026 | Hôm nay là ngày {{ date('d/m/Y') }}</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="position-relative" style="width: 320px;">
        <form method="GET" action="{{ route('ta.dashboard') }}">
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-5 border-secondary-subtle" placeholder="Tìm nhanh tên lớp..." style="border-radius: 6px; height: 42px;" onchange="this.form.submit()">
        </form>
    </div>
    <div class="text-muted fw-semibold">Trợ lý học vụ: <span class="text-dark">{{ Auth::user()->name }}</span></div>
</div>

<div class="table-responsive shadow-sm rounded" style="border-radius: 8px;">
    <table class="table table-bordered text-center align-middle m-0">
        <thead style="background-color: #7A0000; color: white;">
            <tr>
                <th style="width: 60px;">STT</th>
                <th>Tên lớp học</th>
                <th>Số lượng học viên</th>
                <th>Khóa học chuyên môn</th>
                <th>Trạng thái lớp</th>
                <th style="width: 150px;">Hành động</th>
            </tr>
        </thead>

        <tbody>
            @foreach($classes as $key => $class)
            <tr>
                <td>{{ $classes->firstItem() + $key }}</td>
                <td class="text-start fw-semibold text-dark ps-3">{{ $class->class_name }}</td>
                <td class="fw-bold text-secondary">{{ $class->students_count }} học viên</td>
                <td>{{ $class->course->name ?? 'Chưa xác định' }}</td>
                <td>
                    @if($class->status == 'Đang mở')
                        <span class="badge" style="background-color: #D4EDDA; color: #155724; padding: 8px 16px; border-radius: 20px;">
                            Đang mở
                        </span>
                    @else
                        <span class="badge" style="background-color: #F8D7DA; color: #721C24; padding: 8px 16px; border-radius: 20px;">
                            Kết thúc
                        </span>
                    @endif
                </td>
                <td>
                    {{-- Dẫn trực tiếp vào phân hệ Điểm Danh --}}
                    <a href="{{ route('ta.classes.attendance', $class->id) }}" class="btn btn-sm text-white px-3 fw-medium" style="background-color: #990000; border-radius: 5px;">
                        Vào quản lý
                    </a>
                </td>
            </tr>
            @endforeach
            
            @if($classes->isEmpty())
            <tr>
                <td colspan="6" class="py-4 text-muted">Bạn chưa được phân công phụ trách lớp học nào hoặc không tìm thấy kết quả.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $classes->links('pagination::bootstrap-5') }}
</div>
@endsection