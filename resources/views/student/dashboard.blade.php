@extends('layouts.student')

@section('title', 'Học viên - Trang chủ')

@section('student_content')
<div class="text-center mb-5">
    <h3 class="fw-bold" style="letter-spacing: 1px; color: #990000;">BẢNG ĐIỀU KHIỂN HỌC VIÊN</h3>
    <p class="text-muted fw-medium">Học kỳ II - Năm học 2026 | Hôm nay là ngày {{ date('d/m/Y') }}</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="position-relative" style="width: 320px;">
        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
            <i class="bi bi-search"></i>
        </span>
        <input type="text" class="form-control ps-5 border-secondary-subtle" placeholder="Tìm nhanh mã lớp..." style="border-radius: 6px; height: 42px;">
    </div>
    <div class="text-muted fw-semibold">Học viên: <span class="text-dark">{{ Auth::user()->name }}</span></div>
</div>

<div class="table-responsive shadow-sm rounded" style="border-radius: 8px;">
    <table class="table table-bordered text-center align-middle">
        <thead style="background-color: #7A0C0C; color: white;">
            <tr>
                <th>STT</th>
                <th>Tên lớp</th>
                <th>Số học viên</th>
                <th>Khóa học</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>

        <tbody>
            @forelse($classes as $key => $class)
            <tr>
                <td>{{ $key + 1 }}</td>

                <td>{{ $class->class_name }}</td>

                <td>{{ $class->students_count }}</td>

                <td>{{ $class->course->name ?? '' }}</td>

                <td>
                    @if($class->status == 'Đang mở')
                        <span class="badge"
                            style="background-color: #D4EDDA; color: #155724; padding: 8px 16px; border-radius: 20px;">
                            Đang mở
                        </span>
                    @else
                        <span class="badge"
                            style="background-color: #F8D7DA; color: #721C24; padding: 8px 16px; border-radius: 20px;">
                            Kết thúc
                        </span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('student.classes.show', $class->id) }}"
                    class="btn btn-sm text-white px-3"
                    style="background-color: #4A90E2; border-radius: 5px;">
                        Chi tiết
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-muted py-4">Bạn chưa được xếp vào lớp học nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection