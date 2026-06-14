@extends('layouts.admin')

@section('title', 'Quản lý khóa học')

@section('admin_content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0" style="color: #990000; letter-spacing: 0.5px;">DANH SÁCH KHÓA HỌC HIỆN CÓ</h4>
        {{-- Đổi từ <button> thành thẻ <a> để liên kết trang tạo mới --}}
        <a href="{{ route('admin.courses.create') }}" class="btn text-white fw-semibold shadow-sm" style="background-color: #990000; padding: 10px 24px; border-radius: 4px; text-decoration: none;">
            + THÊM KHÓA HỌC MỚI
        </a>
    </div>

    {{-- Khối Tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4 position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-5" placeholder="Tìm theo tên hoặc mô tả khóa học..." style="height: 42px; border-radius: 6px;">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-semibold" style="height: 42px; border-radius: 6px;">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng hiển thị danh sách khóa học --}}
    <div class="card border-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="py-3 text-secondary fw-bold" style="width: 80px;">ID</th>
                        <th class="py-3 text-secondary fw-bold text-start">Tên khóa học</th>
                        <th class="py-3 text-secondary fw-bold text-start">Mô tả tóm tắt</th>
                        <th class="py-3 text-secondary fw-bold">Thời lượng</th>
                        <th class="py-3 text-secondary fw-bold">Chuẩn đầu ra</th>
                        <th class="py-3 text-secondary fw-bold">Số lượng lớp</th>
                        <th class="py-3 text-secondary fw-bold" style="width: 240px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="fw-bold text-secondary">#{{ $course->id }}</td>
                        <td class="text-start fw-bold text-dark">{{ $course->name }}</td>
                        <td class="text-start text-muted" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $course->description ?? 'Chưa có mô tả chi tiết' }}
                        </td>
                        <td><span class="badge bg-light text-dark border px-3 py-2">{{ $course->duration ? $course->duration . ' giờ' : 'N/A' }}</span></td>
                        <td><span class="text-success fw-medium">{{ $course->output_target ?? 'N/A' }}</span></td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger fw-bold px-3 py-2" style="font-size: 13px;">
                                {{ $course->course_classes_count }} Lớp học
                            </span>
                        </td>
                        <td>
                            {{-- Khối cụm hành động: Chi tiết, Sửa, Xóa --}}
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-sm fw-bold px-3 btn-outline-danger" style="border-radius: 4px;">
                                    Chi tiết
                                </a>
                                <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm fw-bold px-3 btn-outline-primary" style="border-radius: 4px;">
                                    Sửa
                                </a>
                                <form method="POST" action="{{ route('admin.courses.destroy', $course->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khóa học này không?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-white fw-bold px-3 btn-danger" style="border-radius: 4px;">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-5 text-muted fw-medium">Hệ thống chưa có dữ liệu khóa học nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $courses->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection