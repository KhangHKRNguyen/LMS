@extends('layouts.admin')

@section('title', 'Quản lý lớp học')

@section('admin_content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0" style="color: #990000; letter-spacing: 0.5px;">QUẢN LÝ DANH SÁCH LỚP HỌC</h4>
        <a href="{{ route('admin.classes.create') }}" class="btn text-white fw-semibold shadow-sm" style="background-color: #990000; padding: 10px 24px; border-radius: 4px;">
            + TẠO LỚP HỌC MỚI
        </a>
    </div>

    {{-- Tìm kiếm --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.classes.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4 position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên lớp học..." class="form-control ps-5 border-secondary-subtle" style="height: 42px; border-radius: 6px;">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-dark fw-semibold px-4" style="height: 42px; border-radius: 6px;">Lọc</button>
                    @if(request('search'))
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-light border ms-1 fw-semibold text-secondary" style="height: 42px; border-radius: 6px;">Xóa bộ lọc</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng hiển thị --}}
    <div class="card border-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table m-0 text-center align-middle table-hover bg-white">
                <thead style="background-color: #990000; color: white;">
                    <tr>
                        <th style="padding: 14px; width: 80px;">STT</th>
                        <th class="text-start">Tên lớp học</th>
                        <th>Khóa học tổng thể</th>
                        <th>Phòng học</th>
                        <th>Thời gian lớp học</th>
                        <th>Trạng thái</th>
                        <th>Sĩ số</th>
                        <th style="width: 260px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $index => $class)
                    <tr>
                        <td>{{ $classes->firstItem() + $index }}</td>
                        <td class="text-start fw-semibold text-secondary ps-3">{{ $class->class_name }}</td>
                        <td>
                            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 12px;">
                                {{ $class->course?->name ?? 'Không rõ khóa học' }}
                            </span>
                        </td>
                        <td><span class="text-muted">{{ $class->room ?? 'Chưa xếp phòng' }}</span></td>
                        <td>
                            @if($class->start_date && $class->end_date)
                                <div class="fw-medium text-dark small">
                                    {{ $class->start_date->format('d/m/Y') }} - {{ $class->end_date->format('d/m/Y') }}
                                </div>
                            @else
                                <span class="text-muted small"><i>Chưa cập nhật</i></span>
                            @endif
                        </td>
                        <td>
                            @if(strtolower($class->status) == 'active' || $class->status == 'Đang mở')
                                <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background-color: #2ec4b6; color: #fff; font-size: 0.75rem; letter-spacing: 0.3px;">
                                    <i class="" style="font-size: 0.5rem; vertical-align: middle;"></i> HOẠT ĐỘNG
                                </span>
                            @else
                                <span class="badge rounded-pill bg-warning text-dark px-3 py-1.5 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.3px;">
                                    TẠM DỪNG
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $class->students_count }}</div>
                            <small class="text-muted">học viên</small>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.classes.members', $class->id) }}" class="btn btn-sm fw-bold px-3 btn-outline-danger" style="border-radius: 4px;">Thành viên</a>
                                <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-sm fw-bold px-3 btn-outline-primary" style="border-radius: 4px;">Sửa</a>
                                <form method="POST" action="{{ route('admin.classes.destroy', $class->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lớp học này không?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-white fw-bold px-3 btn-danger" style="border-radius: 4px;">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-5 text-muted fw-medium">Hệ thống chưa có dữ liệu lớp học nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $classes->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection