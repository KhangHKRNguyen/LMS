@extends('layouts.classroom')

@section('title', 'Danh sách bài tập của lớp')

@section('classroom_content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Bài tập đã giao - Lớp {{ $class->class_name }}</h4>
            <p class="text-muted mb-0 fs-7">Sĩ số: <strong>{{ $class->students_count }}</strong> học viên</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 bg-white rounded">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-red text-uppercase fs-8">
                    <tr>
                        <th class="ps-4 py-3">Tên bài tập (Đề gốc)</th>
                        <th>Buổi học</th>
                        <th>Thời gian mở</th>
                        <th>Hạn nộp</th>
                        <th class="pe-4 text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody class="fs-7">
                    @forelse($distributions as $dist)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $dist->assignment->title }}</td>
                            <td>Ngày {{ \Carbon\Carbon::parse($dist->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                            <td>{{ $dist->open_time?->format('H:i d/m/Y') }}</td>
                            <td>{{ $dist->close_time?->format('H:i d/m/Y') }}</td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('teacher.submissions.index', [$class->id, $dist->id]) }}" 
                                    class="btn btn-sm btn-primary fw-bold shadow-sm">
                                        Xem bài nộp
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                Lớp học này chưa được giao bài tập nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection