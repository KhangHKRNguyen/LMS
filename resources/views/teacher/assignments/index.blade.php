@extends('layouts.classroom')

@section('title', 'Quản lý bài tập & lớp học')

@section('classroom_content')
<div class="container py-4">
    <div class="text-center mb-5">
        {{-- Gọi trực tiếp tên lớp học --}}
        <h3 class="fw-bold text-danger" style="letter-spacing: 1px;">QUẢN LÝ BÀI TẬP - {{ $class->class_name }}</h3>
        <p class="text-muted fw-medium">Xem các bài tập đang hiển thị hoặc khởi tạo cấu hình giao bài từ ngân hàng đề</p>
    </div>

    {{-- Không dùng vòng lặp nữa, hiển thị trực tiếp Card của lớp đó --}}
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 8px;">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center border-0">
            <div>
                <h5 class="fw-bold text-dark m-0"><i class="bi bi-door-open-fill text-danger me-2"></i>{{ $class->class_name }}</h5>
                <small class="text-muted fw-medium">
                    Sĩ số: <strong class="text-dark">{{ $class->students_count }} học viên</strong> | Tổng số: {{ $class->assignments->count() }} bài tập đã giao
                </small>
            </div>
            
            <a href="{{ route('teacher.assignments.create', $class->id) }}" class="btn text-white fw-bold btn-sm px-3" style="background-color: #990000;">
                <i class="bi bi-plus-lg"></i> GIAO BÀI TẬP TỪ NGÂN HÀNG ĐỀ
            </a>
        </div>
        <div class="card-body p-0">
            @if($class->assignments->isEmpty())
                <div class="p-4 text-center text-muted fw-medium">Lớp học này chưa có bài tập nào được giao.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">STT</th>
                                <th style="text-align: left; padding-left: 20px;">Tiêu đề bài tập (Từ ngân hàng đề)</th>
                                <th style="width: 15%;">Loại bài</th>
                                <th style="width: 20%;">Hạn nộp</th>
                                <th style="width: 15%;">Hiển thị</th>
                                <th style="width: 15%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distributions as $dist)
                            <tr>
                                <td>{{ $dist->id }}</td>
                                <td class="text-start fw-bold text-dark">
                                    {{ $dist->assignment->title ?? 'N/A' }}
                                </td>
                                <td>{{ $dist->lessonSession->session_name ?? 'Buổi học' }}</td> <td>{{ $dist->open_time ? $dist->open_time->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $dist->close_time ? $dist->close_time->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $dist->duration_minutes }} phút</td>
                                <td>
                                    <form action="{{ route('teacher.assignments.toggle-visibility', $dist->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm p-0 border-0">
                                            @if($dist->status === 'active')
                                                <span class="badge bg-success"><i class="bi bi-eye-fill"></i> Đang hiện</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="bi bi-eye-slash-fill"></i> Đang ẩn</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('teacher.assignments.show', $dist->id) }}" class="btn btn-sm btn-outline-dark fw-bold">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted fw-medium">Lớp học này chưa có bài tập nào được giao.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection