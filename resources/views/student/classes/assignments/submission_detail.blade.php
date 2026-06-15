@extends('layouts.student_classroom')

@section('title', 'Chi tiết bài nộp')

@section('class_content')
<div class="container-fluid p-0">
    <div class="mb-3">
        <a href="{{ route('student.classes.assignments.detail', [$class->id, $distribution->id]) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Quay lại lịch sử bài làm
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-white rounded">
        <div class="card-header text-dark fw-bold py-3">
            KẾT QUẢ TỔNG QUAN LƯỢT LÀM BÀI #{{ $submission->attempt_number }}
        </div>
        <div class="card-body p-4">
            <div class="row text-center g-3">
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block fw-bold">LISTENING GRADE</small>
                        <h3 class="fw-bold text-primary mt-1">{{ $submission->listening_grade ?? 'N/A' }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block fw-bold">READING GRADE</small>
                        <h3 class="fw-bold text-success mt-1">{{ $submission->reading_grade ?? 'N/A' }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block fw-bold">WRITING GRADE</small>
                        <h3 class="fw-bold text-warning mt-1">{{ $submission->writing_grade ?? 'Chờ chấm' }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block fw-bold">SPEAKING GRADE</small>
                        <h3 class="fw-bold text-danger mt-1">{{ $submission->speaking_grade ?? 'Chờ chấm' }}</h3>
                    </div>
                </div>
            </div>

            @if($submission->teacher_comment)
                <div class="mt-4 p-3 border-start border-warning bg-light rounded">
                    <strong class="text-dark d-block mb-1">Nhận xét từ Giáo viên:</strong>
                    <p class="mb-0 text-muted italic">"{{ $submission->teacher_comment }}"</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold border-bottom py-3 text-dark">
            CHI TIẾT ĐÁP ÁN BÀI LÀM
        </div>
        <div class="card-body p-4">
            @foreach($questions as $question)
                <div class="mb-4 pb-3 border-bottom">
                    <h6 class="fw-bold text-dark">Câu {{ $question->question_number }}: {!! $question->question_text !!}</h6>

                    @if($question->question_type === 'trac_nghiem')
                        @php $studentChoice = $mcAnswers->get($question->id); @endphp
                        <div class="row mt-2 g-2">
                            @foreach($question->options as $option)
                                @php 
                                    $isPicked = $studentChoice && $studentChoice->question_option_id == $option->id;
                                    $bgClass = '';
                                    if ($option->is_correct) $bgClass = 'bg-success text-white border-success';
                                    elseif ($isPicked && !$option->is_correct) $bgClass = 'bg-danger text-white border-danger';
                                @endphp
                                <div class="col-md-6">
                                    <div class="p-2 border rounded fs-7 {{ $bgClass }}">
                                        <strong>{{ $option->option_letter }}.</strong> {{ $option->option_content }}
                                        @if($isPicked) <span class="badge bg-dark ms-2 text-white">Bạn chọn</span> @endif
                                        @if($option->is_correct) <span class="badge bg-white text-success ms-2 fw-bold">Đáp án đúng</span> @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @elseif($question->question_type === 'dien_tu')
                        @php 
                            $fbAnswer = $fbAnswers->get($question->id); 
                            $details = $fbAnswer ? ($fbDetails[$fbAnswer->id] ?? collect())->keyBy('blank_order') : collect();
                        @endphp
                        <div class="mt-2 bg-light p-3 rounded border">
                            @foreach($question->keywords as $keyword)
                                @php $studentInput = $details->get($keyword->blank_order); @endphp
                                <div class="mb-2 fs-7">
                                    <strong>Ô trống #{{ $keyword->blank_order }}:</strong> 
                                    Từ khóa đúng: <span class="text-success fw-bold">{{ $keyword->correct_keyword }}</span> | 
                                    Học viên điền: 
                                    <span class="{{ $studentInput && $studentInput->is_correct ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                        {{ $studentInput ? $studentInput->student_input : '(Bỏ trống)' }}
                                    </span>
                                    @if($studentInput && $studentInput->is_correct)
                                        <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-danger ms-1"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    @elseif($question->question_type === 'writing')
                        @php $writing = $writingAnswers->get($question->id); @endphp
                        <div class="mt-2">
                            <label class="fs-7 text-muted fw-bold d-block mb-1">Bài viết luận học viên đã nộp (Số từ: {{ $writing->word_count ?? 0 }}):</label>
                            <div class="p-3 border rounded bg-light fs-7 text-dark whitespace-pre-wrap">{{ $writing ? $writing->essay_content : 'Không nộp nội dung.' }}</div>
                        </div>

                    @elseif($question->question_type === 'speaking')
                        @php $speaking = $speakingAnswers->get($question->id); @endphp
                        <div class="mt-2">
                            <label class="fs-7 text-muted fw-bold d-block mb-1">File ghi âm học viên đã nộp:</label>
                            @if($speaking && $speaking->audio_file_path)
                                <audio controls class="w-100 mt-1" style="max-height: 40px;">
                                    <source src="{{ asset('storage/' . $speaking->audio_file_path) }}" type="audio/mpeg">
                                    Trình duyệt không hỗ trợ nghe Audio.
                                </audio>
                            @else
                                <p class="text-danger fs-7 m-0 italic">Không có file ghi âm được lưu trữ.</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="card shadow-sm border-0 mt-4 bg-white rounded">
        <div class="card-body p-4 text-center">
            <h5 class="fw-bold text-dark mb-2">Bạn có thắc mắc hoặc phản hồi về bài chấm này?</h5>
            <p class="text-muted fs-7 mb-3">Hệ thống hỗ trợ trao đổi trực tiếp với Giảng viên/Trợ giảng phụ trách lớp học thông qua hộp thoại riêng.</p>
            <a href="{{ route('student.classes.assignments.submissions.feedback', [$class->id, $distribution->id, $submission->id]) }}" class="btn btn-primary fw-bold px-4 shadow-sm">
                Mở khung chat phản hồi
            </a>
        </div>
    </div>
</div>
@endsection