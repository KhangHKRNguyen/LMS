@extends('layouts.classroom')

@section('title', 'Quản lý bài tập & lớp học')

@section('classroom_content')
<div class="container py-4">
    <div class="text-center mb-5">
        {{-- Gọi trực tiếp tên lớp học --}}
        <h3 class="fw-bold text-danger" style="letter-spacing: 1px;">QUẢN LÝ BÀI TẬP - {{ $class->class_name }}</h3>
        <p class="text-muted fw-medium">Xem các bài tập đang hiển thị hoặc khởi tạo cấu hình giao bài từ ngân hàng đề</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                            @foreach($class->assignments as $idx => $assignment)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td style="text-align: left; padding-left: 20px;" class="fw-bold text-dark">
                                    {{ $assignment->exam->title ?? $assignment->title }}
                                </td>
                                <td>
                                    <span class="badge {{ ($assignment->exam ?? $assignment)->isQuiz() ? 'bg-primary' : 'bg-info text-dark' }}">
                                        {{ method_exists($assignment, 'typeLabel') ? $assignment->typeLabel() : (($assignment->exam ?? null) ? $assignment->exam->typeLabel() : 'Trắc nghiệm') }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted fw-semibold">
                                        {{ $assignment->due_time ? $assignment->due_time->format('d/m/Y H:i') : 'Không giới hạn' }}
                                    </small>
                                </td>
                                <td>
                                    <form action="{{ route('teacher.assignments.toggle-visibility', $assignment->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm p-0 border-0">
                                            @if($assignment->is_visible)
                                                <span class="badge bg-success"><i class="bi bi-eye-fill"></i> Đang hiện</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="bi bi-eye-slash-fill"></i> Đang ẩn</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('teacher.assignments.show', $assignment->id) }}" class="btn btn-sm btn-outline-dark fw-bold">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection