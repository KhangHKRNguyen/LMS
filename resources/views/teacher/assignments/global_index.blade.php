@extends('layouts.classroom')

@section('title', 'Danh sách bài tập đã giao')

@section('classroom_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark m-0">Quản lý bài tập đã giao</h3>
    {{-- Nút điều hướng qua Ngân hàng đề để chọn bài giao --}}
    <a href="{{ route('teacher.exams.index') }}" class="btn btn-primary fw-bold shadow-sm">
        <i class="bi bi-plus-circle"></i> Giao bài tập mới
    </a>
</div>

<div class="card shadow-sm border-0" style="border-radius: 8px;">
    <div class="card-body p-4 bg-white">
        <div class="table-responsive" style="border-radius: 6px;">
            <table class="table table-bordered align-middle text-center m-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên bài tập / Đề thi</th>
                        {{-- Ẩn cột lớp học nhận nếu đang xem trong bối cảnh một lớp cụ thể --}}
                        @if(!$class)
                            <th>Lớp học nhận</th>
                        @endif
                        <th>Thời gian mở</th>
                        <th>Hạn nộp</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distributions as $item)
                    <tr>
                        <td class="text-start fw-bold">{{ $item->assignment->title ?? $item->exam->title ?? 'N/A' }}</td>
                        {{-- Ẩn dữ liệu cột lớp học nhận tương ứng --}}
                        @if(!$class)
                            <td class="fw-medium text-secondary">{{ $item->lessonSession->courseClass->class_name ?? 'N/A' }}</td>
                        @endif
                        <td class="fs-7 text-muted">{{ $item->open_time ? \Carbon\Carbon::parse($item->open_time)->format('d/m/Y H:i') : '---' }}</td>
                        <td class="fs-7 text-danger fw-medium">{{ $item->close_time ? \Carbon\Carbon::parse($item->close_time)->format('d/m/Y H:i') : '---' }}</td>
                        <td>
                            <form action="{{ route('teacher.assignments.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy giao bài này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    Hủy giao
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        {{-- Tự động tính toán colspan để bảng không bị lệch khung viền --}}
                        <td colspan="{{ $class ? 4 : 5 }}" class="py-4 text-muted fw-medium">
                            {{ $class ? 'Lớp học này chưa được giao bài tập nào.' : 'Bạn chưa giao bài tập nào cho các lớp học.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection