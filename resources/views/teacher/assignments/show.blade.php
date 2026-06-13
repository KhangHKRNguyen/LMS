@extends('layouts.classroom')

@section('title', 'Chi tiết bài tập đã giao')

@section('classroom_content')
<div class="container py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <a href="{{ route('teacher.assignments.index') }}" class="text-decoration-none text-secondary fw-medium">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách lớp
        </a>
        <span class="badge {{ $assignment->is_visible ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
            Trạng thái: {{ $assignment->is_visible ? 'Đang mở hiển thị' : 'Đang ẩn với học sinh' }}
        </span>
    </div>

    {{-- Cấu hình phân phối của Bài tập tại Lớp học này --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 8px;">
        <div class="card-body p-4 bg-white" style="border-left: 5px solid #990000; border-radius: 8px;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <small class="text-muted text-uppercase fw-bold">Chi tiết bài kiểm tra được giao</small>
                    <h4 class="fw-bold text-dark mt-1 mb-2">{{ $assignment->exam->title ?? $assignment->title }}</h4>
                    <p class="text-muted m-0 fs-7">
                        Hình thức: <strong class="text-primary">{{ method_exists($assignment, 'typeLabel') ? $assignment->typeLabel() : (($assignment->exam ?? null) ? $assignment->exam->typeLabel() : 'Trắc nghiệm') }}</strong> &nbsp;|&nbsp; 
                        Lớp học nhận bài: <strong class="text-dark">{{ $assignment->courseClass->class_name }}</strong> &nbsp;|&nbsp;
                        Số bài học viên đã nộp: <strong class="text-danger">{{ $assignment->submissions_count ?? 0 }} bài</strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="d-block text-muted small fw-medium">Hạn cuối nộp bài:</span>
                    <span class="fw-bold text-danger fs-5">{{ $assignment->due_time ? $assignment->due_time->format('d/m/Y H:i') : 'Không giới hạn' }}</span>
                </div>
            </div>
            
            {{-- Nếu đề thi gốc trong ngân hàng đề có chứa file tài liệu đính kèm --}}
            @if(($assignment->exam->file_path ?? $assignment->file_path))
            <div class="mt-3 pt-3 border-top">
                <a href="{{ route('teacher.exams.download', $assignment->exam_id) }}" class="btn btn-sm btn-outline-danger fw-bold">
                    <i class="bi bi-download"></i> Tải đề bài / File nghe đính kèm từ Ngân hàng đề
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- HIỂN THỊ NỘI DUNG CHI TIẾT ĐỀ THI LẤY TỪ NGÂN HÀNG ĐỀ --}}
    @if(($assignment->exam ?? $assignment)->isEssay())
        {{-- Khối tự luận (IELTS Writing / Speaking) --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light fw-bold py-3 text-dark">
                <i class="bi bi-file-earmark-text-fill text-danger me-2"></i>NỘI DUNG ĐỀ BÀI TỰ LUẬN
            </div>
            <div class="card-body p-4">
                <div class="p-3 bg-light rounded text-dark" style="white-space: pre-line; line-height: 1.6;">
                    {{ $assignment->exam->content ?? $assignment->content }}
                </div>
            </div>
        </div>
    @else
        {{-- Khối trắc nghiệm (IELTS Listening / Reading / Grammar Quiz) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0 text-dark">
                <i class="bi bi-list-ol text-danger me-2"></i>DANH SÁCH CÂU HỎI TRONG ĐỀ THI ĐÃ GIAO ({{ ($assignment->exam->questions ?? $assignment->questions)->count() }} câu)
            </h5>
            <a href="{{ route('teacher.exams.export', $assignment->exam_id) }}" class="btn btn-sm btn-success fw-bold">
                <i class="bi bi-file-earmark-spreadsheet"></i> Xuất File câu hỏi (.CSV)
            </a>
        </div>

        {{-- Duyệt mảng câu hỏi từ quan hệ dữ liệu Ngân hàng đề gốc --}}
        @forelse(($assignment->exam->questions ?? $assignment->questions) as $index => $question)
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 6px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="fw-bold text-dark">Câu {{ $index + 1 }}: {{ $question->question_text }}</span>
                    <span class="badge bg-light text-secondary border fw-medium px-2 py-1 fs-7">Trắc nghiệm</span>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 py-2 px-3 rounded border {{ $question->correct_option === 'A' ? 'border-success bg-success-subtle text-success fw-bold' : 'bg-light text-dark' }}">
                        <span class="me-2 fw-bold">A.</span> {{ $question->option_a }} {!! $question->correct_option === 'A' ? '<i class="bi bi-check-lg text-success ms-2"></i>' : '' !!}
                    </div>
                    <div class="col-md-6 py-2 px-3 rounded border {{ $question->correct_option === 'B' ? 'border-success bg-success-subtle text-success fw-bold' : 'bg-light text-dark' }}">
                        <span class="me-2 fw-bold">B.</span> {{ $question->option_b }} {!! $question->correct_option === 'B' ? '<i class="bi bi-check-lg text-success ms-2"></i>' : '' !!}
                    </div>
                    <div class="col-md-6 py-2 px-3 rounded border {{ $question->correct_option === 'C' ? 'border-success bg-success-subtle text-success fw-bold' : 'bg-light text-dark' }}">
                        <span class="me-2 fw-bold">C.</span> {{ $question->option_c }} {!! $question->correct_option === 'C' ? '<i class="bi bi-check-lg text-success ms-2"></i>' : '' !!}
                    </div>
                    <div class="col-md-6 py-2 px-3 rounded border {{ $question->correct_option === 'D' ? 'border-success bg-success-subtle text-success fw-bold' : 'bg-light text-dark' }}">
                        <span class="me-2 fw-bold">D.</span> {{ $question->option_d }} {!! $question->correct_option === 'D' ? '<i class="bi bi-check-lg text-success ms-2"></i>' : '' !!}
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-5 text-center bg-white rounded border text-muted fw-medium">
            Bài kiểm tra trắc nghiệm này chưa có câu hỏi nào trong cơ sở dữ liệu gốc.
        </div>
        @endforelse
    @endif
</div>
@endsection