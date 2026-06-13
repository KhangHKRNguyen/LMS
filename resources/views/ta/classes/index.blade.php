@extends('layouts.app')

@section('title', 'Danh sách lớp học phụ trách')

@section('content')
<div class="text-center mb-5">
    <h3 class="text-danger fw-bold text-uppercase" style="color: var(--primary-color) !important; letter-spacing: 1px;">
        XIN CHÀO, TRỢ GIẢNG
    </h3>
    <p class="text-muted fw-medium mt-2">Hôm nay là {{ \Carbon\Carbon::now()->locale('vi')->translatedFormat('l, ngày d tháng m năm Y') }}</p>
</div>

<form action="{{ route('ta.classes.index') }}" method="GET" class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
    <div class="position-relative" style="width: 340px;">
        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
            <i class="bi bi-search"></i>
        </span>
        <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-5 border-secondary-subtle" placeholder="Tìm kiếm theo mã hoặc tên lớp..." style="border-radius: 6px; height: 42px; font-size: 14px;">
    </div>
    
    <div class="d-flex gap-2">
        <select name="status" class="form-select border-secondary-subtle" style="width: 180px; height: 42px; border-radius: 6px; font-size: 14px;" onchange="this.form.submit()">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
            <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
        </select>
        <button type="submit" class="btn btn-primary px-3" style="border-radius: 6px; height: 42px;">Lọc</button>
    </div>
</form>

<div class="table-responsive shadow-sm" style="border-radius: 8px;">
    <table class="table-arena table text-center align-middle m-0 bg-white">
        <thead style="background-color: #800000; color: #fff;">
            <tr>
                <th style="width: 60px;">STT</th>
                <th style="width: 140px;">Mã lớp</th>
                <th>Tên lớp</th>
                <th style="width: 150px;">Số học viên</th>
                <th>Khóa học</th>
                <th style="width: 160px;">Trạng thái</th>
                <th style="width: 150px;">Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classes as $index => $class)
            <tr>
                <td>{{ $classes->firstItem() + $index }}</td>
                <td class="fw-bold text-dark">#{{ $class->id }}</td>
                <td class="fw-medium text-start ps-4">{{ $class->class_name }}</td>
                <td>
                    <strong class="text-dark">{{ $class->students_count }}</strong> học viên
                </td>
                <td class="text-muted fw-medium">{{ $class->course->name ?? 'Chưa gán khóa học' }}</td>
                <td>
                    @if($class->status === 'active')
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill" style="font-size: 12px; min-width: 110px; display: inline-block;">Đang hoạt động</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill" style="font-size: 12px; min-width: 110px; display: inline-block;">Kết thúc</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('ta.classes.attendance', $class->id) }}" class="btn btn-sm text-white fw-medium px-3 py-1" style="background-color: #0d6efd; border-radius: 4px; font-size: 13px;">
                        Chi tiết
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center p-4 text-muted">Không tìm thấy dữ liệu lớp học phù hợp.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center align-items-center mt-4">
    {{ $classes->appends(request()->query())->links() }}
</div>
@endsection