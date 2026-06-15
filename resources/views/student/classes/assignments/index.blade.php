@extends('layouts.student_classroom')

@section('title', 'Bài tập lớp học')

@section('class_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-uppercase m-0" style="color: #990000;">
        Danh sách bài tập được giao
    </h6>
    <span class="badge bg-secondary px-3 py-2">Tổng số: {{ $distributions->count() }} bài</span>
</div>

<div class="table-responsive shadow-sm rounded bg-white">
    <table class="table table-hover table-bordered m-0 text-center align-middle" style="font-size: 14px;">
        <thead class="text-white text-nowrap" style="background-color: #990000;">
            <tr>
                <th style="width: 50px;">STT</th>
                <th>Buổi học</th>
                <th>Tên bài tập</th>
                <th>Loại bài tập</th>
                <th>Thời gian làm</th>
                <th>Thời gian mở</th>
                <th>Thời gian đóng</th>
                <th>Số lần làm bài</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distributions as $index => $dist)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <span class="fw-semibold text-secondary">
                            Buổi ngày {{ $dist->lessonSession ? \Carbon\Carbon::parse($dist->lessonSession->lesson_date)->format('d/m/Y') : '---' }}
                        </span>
                    </td>
                    <td class="text-start fw-bold text-dark">{{ $dist->assignment->title }}</td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $dist->assignment->assignmentType->name ?? 'Chưa phân loại' }}
                        </span>
                    </td>
                    <td>{{ $dist->duration_minutes ? $dist->duration_minutes . ' phút' : 'Không giới hạn' }}</td>
                    <td class="fs-7 text-secondary">{{ $dist->open_time ? $dist->open_time->format('H:i d/m/Y') : '---' }}</td>
                    <td class="fs-7 text-secondary">{{ $dist->close_time ? $dist->close_time->format('H:i d/m/Y') : '---' }}</td>
                    <td>
                        @php
                            $submissionCount = $dist->submissions->count();
                            $maxAttempts = $dist->max_attempts;
                        @endphp
                        @if($maxAttempts)
                            <span class="badge bg-info">{{ $submissionCount }}/{{ $maxAttempts }}</span>
                        @else
                            <span class="badge bg-secondary">{{ $submissionCount }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('student.classes.assignments.detail', ['class' => $class->id, 'distribution' => $dist->id]) }}" 
                           class="btn btn-sm btn-outline-dark fw-bold px-3">
                             Chi tiết
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                        Hiện tại chưa có bài tập nào được giao cho lớp học này.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection