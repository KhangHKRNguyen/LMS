@extends('layouts.student_classroom')

@section('title', 'Không gian bài tập')

@section('class_content')
<div class="container-fluid p-0">
    
    {{-- Khối 1: THÔNG TIN PHÍA TRÊN & NÚT QUAY LẠI --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('student.classes.show', $class->id) }}" class="btn btn-sm btn-light border fw-semibold text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách bài tập
        </a>
        <span class="fs-7 text-muted">Workspace học viên</span>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 bg-white rounded" style="border-left: 5px solid #990000;">
            <div class="row g-3">
                <div class="col-md-7">
                    <small class="text-uppercase text-muted fw-bold d-block mb-1" style="letter-spacing: 0.5px;">Tên bài tập / Đề thi</small>
                    <h4 class="fw-bold text-dark mb-2">{{ $distribution->assignment->title }}</h4>
                    <p class="text-muted mb-0 fs-7">
                        Học viên thực hiện: <strong class="text-dark">{{ Auth::user()->name }}</strong>
                    </p>
                </div>
                <div class="col-md-5 border-start-md ps-md-4">
                    <ul class="list-unstyled mb-0 fs-7 text-secondary">
                        <li class="mb-1">
                            Thời gian làm bài: 
                            <strong class="text-dark">{{ $distribution->duration_minutes ? $distribution->duration_minutes . ' phút' : 'Không giới hạn' }}</strong>
                        </li>
                        <li class="mb-1">
                            Thời gian mở: 
                            <strong class="text-dark">{{ $distribution->open_time ? $distribution->open_time->format('H:i - d/m/Y') : 'Bất cứ lúc nào' }}</strong>
                        </li>
                        <li class="mb-1">
                            Thời gian đóng: 
                            <strong class="text-dark">{{ $distribution->close_time ? $distribution->close_time->format('H:i - d/m/Y') : 'Không đóng' }}</strong>
                        </li>
                        <li>
                            Giới hạn số lần làm: 
                            <strong class="text-dark">{{ $submissions->count() }} / {{ $distribution->max_attempts }} lượt</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Khối 2: DANH SÁCH CÁC LẦN LÀM BÀI TRƯỚC ĐÓ --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 fw-bold text-dark text-uppercase">
                Lịch sử các lần nộp bài
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped text-center align-middle m-0" style="font-size: 14px;">
                <thead class="table-light text-secondary fw-semibold">
                    <tr>
                        <th style="width: 70px;">Lần làm</th>
                        <th>Ngày giờ nộp bài</th>
                        <th style="background-color: #f8f9fa;">Listening</th>
                        <th style="background-color: #f8f9fa;">Reading</th>
                        <th style="background-color: #f8f9fa;">Writing</th>
                        <th style="background-color: #f8f9fa;">Speaking</th>
                        <th class="table-primary text-primary">Overall Score</th>
                        <th>Trạng thái</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $sub)
                        <tr>
                            <td class="fw-bold text-dark">{{ $sub->attempt_number }}</td>
                            <td class="text-secondary fs-7">
                                {{ $sub->submission_time ? $sub->submission_time->format('H:i:s d/m/Y') : $sub->created_at->format('H:i:s d/m/Y') }}
                            </td>
                            {{-- Điểm số IELTS hiển thị định dạng từ 0 - 9 --}}
                            <td class="fw-medium text-dark" style="background-color: #f8f9fa;">{{ $sub->listening_grade !== null ? number_format($sub->listening_grade, 1) : '---' }}</td>
                            <td class="fw-medium text-dark" style="background-color: #f8f9fa;">{{ $sub->reading_grade !== null ? number_format($sub->reading_grade, 1) : '---' }}</td>
                            <td class="fw-medium text-dark" style="background-color: #f8f9fa;">{{ $sub->writing_grade !== null ? number_format($sub->writing_grade, 1) : '---' }}</td>
                            <td class="fw-medium text-dark" style="background-color: #f8f9fa;">{{ $sub->speaking_grade !== null ? number_format($sub->speaking_grade, 1) : '---' }}</td>
                            
                            <td class="fw-bold text-primary fs-6 table-primary">
                                {{ $sub->total_grade !== null ? number_format($sub->total_grade, 1) : '---' }}
                            </td>
                            <td>
                                @if($sub->status === 'graded')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Đã chấm điểm</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Chờ chấm điểm</span>
                                @endif
                            </td>
                            <td>
                                @if($distribution->close_time && now()->gt($distribution->close_time))
                                    <a href="{{ route('student.classes.assignments.submissions.show', [$class->id, $distribution->id, $submission->id]) }}" class="btn btn-xs btn-info">
                                        Xem chi tiết
                                    </a>
                                @else
                                    <button class="btn btn-xs btn-secondary" disabled>Chỉ xem được khi hết hạn</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-muted py-4">Bạn chưa thực hiện lượt làm bài nào cho bài tập này.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Khối 3: BUTTON LOGIC LÀM BÀI / LÀM LẠI BÀI --}}
    <div class="card shadow-sm border-0 bg-light rounded">
        <div class="card-body p-4 text-center">
            @if($buttonState['status'] === 'disabled')
                <div class="mb-2 text-danger fw-medium fs-7">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Bạn không đủ điều kiện thực hiện làm bài: Điều kiện thời gian hoặc giới hạn lượt.
                </div>
                <button class="btn {{ $buttonState['class'] }} btn-lg fw-bold px-5 py-2" disabled>
                    {{ $buttonState['text'] }}
                </button>
            @else
                <div class="mb-2 text-success fw-medium fs-7">
                    <i class="bi bi-check-circle-fill me-1"></i> Bạn đủ điều kiện thực hiện làm bài. Vui lòng bấm nút bên dưới để bắt đầu.
                </div>
                <a href="{{ $buttonState['link'] }}" class="btn {{ $buttonState['class'] }} btn-lg fw-bold px-5 py-2 shadow-sm">
                    {{ $buttonState['text'] }}
                </a>
            @endif
        </div>
    </div>

</div>
@endsection