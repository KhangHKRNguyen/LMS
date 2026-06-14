@extends('layouts.teacher')

@section('title', 'Chi tiết cấu trúc đề thi')

@section('teacher_content')
<div class="container py-4" style="max-width: 900px;">
    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('teacher.exams.index') }}" class="text-decoration-none text-secondary"><i class="bi bi-arrow-left"></i> Trở về ngân hàng đề</a>
        <a href="{{ route('teacher.exams.edit', $exam->id) }}" class="btn btn-sm btn-warning fw-bold px-3">SỬA ĐỀ THI NÀY</a>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-left: 5px solid #800000;">
        <div class="card-body p-4">
            <span class="badge bg-secondary mb-2">{{ $exam->assignmentType->name }}</span>
            <h3 class="fw-bold text-dark mb-2">{{ $exam->title }}</h3>
            <p class="text-muted small mb-3">Ngày khởi tạo: {{ $exam->created_at->format('d/m/Y H:i') }} | Tác giả: {{ $exam->user->name ?? 'Hệ thống' }}</p>
            
            @if($exam->description)
                <div class="bg-light p-3 rounded mb-3 small text-secondary">
                    <strong>Hướng dẫn làm bài:</strong><br>{!! nl2br(e($exam->description)) !!}
                </div>
            @endif

            @if($exam->file_path)
                <div class="d-flex align-items-center gap-2 alert alert-info py-2 px-3 m-0 small">
                    <i class="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                    <span>Đề thi có file đính kèm chính thức phục vụ làm bài.</span>
                    <a href="{{ asset('storage/' . $exam->file_path) }}" target="_blank" class="ms-auto btn btn-sm btn-primary py-0 px-2">Tải xuống file đề bài</a>
                </div>
            @endif
        </div>
    </div>

    <h5 class="fw-bold mb-3 text-secondary">CHI TIẾT ĐỀ THI ({{ $exam->questions->count() }} câu hỏi)</h5>
    
    @foreach($exam->questions as $question)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                    <span class="fw-bold text-dark">Câu {{ $question->question_number }}</span>
                    <div>
                        <span class="badge bg-primary me-1">Kỹ năng: {{ $question->skill->name ?? 'Chưa rõ' }}</span>
                    </div>
                </div>

                <p class="fw-medium text-dark bg-light p-2 rounded">{!! nl2br(e($question->question_text)) !!}</p>

                @if($question->question_type === 'trac_nghiem')
                    <div class="row g-2 mt-2 ps-3">
                        @foreach($question->options as $option)
                            <div class="col-md-6 d-flex align-items-center gap-2 p-1 border rounded {{ $option->is_correct ? 'bg-success-subtle border-success text-success fw-bold' : '' }}">
                                <span>{{ $option->option_letter }}.</span>
                                <span>{{ $option->option_content }}</span>
                                @if($option->is_correct)
                                    <i class="bi bi-check-circle-fill ms-auto me-2"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($question->question_type === 'dien_tu')
                    <div class="mt-2 p-2 bg-light rounded border border-success">
                        <span class="small d-block fw-bold text-success mb-1"><i class="bi bi-key-fill"></i> Đáp án điền từ đúng xếp theo thứ tự:</span>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($question->keywords as $keyword)
                                <span class="badge bg-success py-2 px-3 fs-6">Ô [{{ $keyword->blank_order }}]: {{ $keyword->correct_keyword }}</span>
                            @endforeach
                        </div>
                    </div>
                @elseif($question->question_type === 'speaking')
                    <div class="mt-2 text-danger small">
                        <i class="bi bi-mic-fill"></i> Học viên thực hiện thu âm trực tiếp trên hệ thống (Thời gian tối đa: {{ $question->max_recording_time ?? 120 }} giây).
                    </div>
                @elseif($question->question_type === 'writing')
                    <div class="mt-2 text-muted small">
                        <i class="bi bi-pencil-square"></i> Bài luận tự do (Writing Task) – Giảng viên sẽ chấm điểm thủ công sau khi học viên nộp bài.
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection