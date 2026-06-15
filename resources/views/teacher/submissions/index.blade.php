@extends('layouts.classroom')

@section('title', 'Danh sách bài nộp')

@section('classroom_content')
<div class="container-fluid p-0">
    
    {{-- Thanh điều hướng & Số lượng tổng quát --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Quản lý bài nộp học viên</h4>
            <p class="text-muted mb-0 fs-7">
                Bài tập: <strong class="text-dark">{{ $distribution->assignment->title }}</strong> 
                | Buổi học: <strong class="text-primary">Ngày {{ \Carbon\Carbon::parse($distribution->lessonSession->lesson_date)->format('d/m/Y') }}</strong>
            </p>
        </div>
        
        {{-- Khối hiển thị Tổng số bài nộp phía trên bên phải --}}
        <div class="bg-white border rounded shadow-sm px-4 py-2 text-center">
            <small class="text-uppercase text-muted fw-bold d-block fs-8" style="letter-spacing: 0.5px;">Tổng số bài nộp</small>
            <h3 class="fw-black text-danger m-0 font-monospace">{{ $totalSubmissions }}</h3>
        </div>
    </div>

    {{-- Bảng dữ liệu chính --}}
    <div class="card shadow-sm border-0 bg-white rounded">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-red text-uppercase fs-8">
                    <tr>
                        <th class="ps-4 py-3" style="width: 70px;">STT</th>
                        <th>Mã học viên</th>
                        <th>Tên học viên</th>
                        <th>Buổi học giao</th>
                        <th>Thời gian nộp</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Điểm (Overall)</th>
                        <th class="pe-4 text-end" style="width: 150px;">Hành động</th>
                    </tr>
                </thead>
                <tbody class="fs-7">
                    @forelse($submissions as $index => $sub)
                        <tr>
                            <td class="ps-4 fw-medium text-secondary">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $sub->user->id }}</td>
                            <td class="fw-bold text-dark">{{ $sub->user->name }}</td>
                            <td>
                                <span class="text-secondary">
                                    {{ \Carbon\Carbon::parse($distribution->lessonSession->lesson_date)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($sub->submission_time)->format('H:i:s d/m/Y') }}</small>
                            </td>
                            <td class="text-center">
                                @if($sub->status === 'graded')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded">Đã chấm</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded">Chưa chấm</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold text-primary fs-6">
                                {{ $sub->total_grade !== null ? number_format($sub->total_grade, 1) : '—' }}
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('teacher.submissions.grade', [$class->id, $sub->id]) }}" 
                                   class="btn btn-sm {{ $sub->status === 'graded' ? 'btn-outline-secondary' : 'btn-danger shadow-sm' }} fw-bold px-3">
                                    Chấm bài
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5 fs-6">
                                Hiện tại chưa có học viên nào nộp bài cho đợt giao bài này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection