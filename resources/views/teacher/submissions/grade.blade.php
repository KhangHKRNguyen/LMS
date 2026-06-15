@extends('layouts.classroom')

@section('title', 'Chấm điểm bài nộp')

@section('classroom_content')
<div class="container-fluid p-0">
    
    {{-- Header thông tin bài làm - Đã đổi từ border-primary sang màu đỏ đô Arena tinh tế hơn --}}
    <div class="card shadow-sm border-0 mb-4 bg-white border-start border-4 rounded" style="border-left-color: #990000 !important;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-arena text-uppercase mb-2">Workspace chấm điểm giáo viên</span>
                <h4 class="fw-bold m-0 text-dark">{{ $distribution->assignment->title }}</h4>
                <p class="text-muted m-0 fs-7 mt-1">
                    Học viên: <strong class="text-arena">{{ $submission->user->name }}</strong> 
                    | Lượt làm bài: <strong>#{{ $submission->attempt_number }}</strong>
                </p>
            </div>
            <a href="{{ route('teacher.submissions.index', [$class->id, $distribution->id]) }}" class="btn btn-sm btn-outline-secondary fw-semibold bg-white shadow-sm">
                Quay lại danh sách
            </a>
        </div>
    </div>

    <form action="{{ route('teacher.submissions.post_grade', [$class->id, $submission->id]) }}" method="POST">
        @csrf
        <div class="row g-4">
            
            {{-- CỘT TRÁI: Nội dung chi tiết các câu hỏi và so khớp đáp án --}}
            <div class="col-lg-8">
                @foreach($questions as $index => $question)
                    <div class="card shadow-sm border-0 mb-4 bg-white rounded" id="q-box-{{ $question->id }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-secondary px-3 py-1.5 fw-bold fs-7">Câu {{ $question->question_number }}</span>
                            </div>
                            
                            {{-- Nội dung câu hỏi --}}
                            <div class="text-dark fw-semibold mb-3 fs-6 whitespace-pre-wrap">{!! $question->question_text !!}</div>

                            {{-- 1. HIỂN THỊ DẠNG TRẮC NGHIỆM --}}
                            @if($question->question_type === 'trac_nghiem')
                                @php 
                                    $studentAns = $mcAnswers->get($question->id);
                                    $selectedOptionId = $studentAns ? $studentAns->question_option_id : null;
                                @endphp
                                <div class="row g-2">
                                    @foreach($question->options as $option)
                                        @php
                                            $isStudentSelected = ($selectedOptionId == $option->id);
                                            $isCorrectOption = $option->is_correct;
                                            
                                            // Thiết lập màu sắc viền/nền phân biệt
                                            $bgClass = 'bg-light border';
                                            $iconHtml = '';
                                            if ($isCorrectOption) {
                                                $bgClass = 'bg-success-subtle border-success text-success fw-bold';
                                                $iconHtml = ' <span class="badge bg-success ms-2">ĐÁP ÁN ĐÚNG</span>';
                                            } elseif ($isStudentSelected && !$isCorrectOption) {
                                                $bgClass = 'bg-danger-subtle border-danger text-danger fw-bold';
                                                $iconHtml = ' <span class="badge bg-danger ms-2">HỌC VIÊN CHỌN SAI</span>';
                                            }
                                        @endphp
                                        <div class="col-12">
                                            <div class="p-3 rounded d-flex align-items-center {{ $bgClass }}">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input" type="radio" disabled {{ $isStudentSelected ? 'checked' : '' }}>
                                                    <label class="form-check-label ms-2 text-dark">
                                                        <strong>{{ $option->option_letter }}.</strong> {{ $option->option_content }}
                                                    </label>
                                                </div>
                                                {!! $iconHtml !!}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            {{-- 2. HIỂN THỊ DẠNG ĐIỀN TỪ (FILL BLANK) --}}
                            @elseif($question->question_type === 'dien_tu')
                                @php
                                    $studentFbMẹ = $fbAnswers->get($question->id);
                                    $details = $studentFbMẹ ? ($fbDetails[$studentFbMẹ->id] ?? collect()) : collect();
                                @endphp
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered align-middle mb-0 fs-7">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th style="width: 100px;">Ô trống</th>
                                                <th>Học viên điền</th>
                                                <th>Đáp án chính xác</th>
                                                <th style="width: 120px;">Kết quả</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($question->keywords as $kw)
                                                @php
                                                    $detail = $details->firstWhere('blank_order', $kw->blank_order);
                                                    $studentInput = $detail ? $detail->student_input : '—';
                                                    $isOk = $detail ? $detail->is_correct : false;
                                                @endphp
                                                <tr>
                                                    <td class="text-center fw-bold">Ô số {{ $kw->blank_order }}</td>
                                                    <td class="{{ $isOk ? 'text-success fw-bold' : 'text-danger fw-bold bg-danger-subtle' }}">
                                                        {{ $studentInput }}
                                                    </td>
                                                    <td class="text-dark fw-bold bg-success-subtle">{{ $kw->correct_keyword }}</td>
                                                    <td class="text-center">
                                                        @if($isOk)
                                                            <span class="text-success">Đúng</span>
                                                        @else
                                                            <span class="text-danger">Sai</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            {{-- 3. CHẤM ĐIỂM TỰ LUẬN WRITING --}}
                            @elseif($question->question_type === 'writing')
                                @php $writing = $writingAnswers->get($question->id); @endphp
                                <div class="mt-2">
                                    <label class="fw-bold text-muted fs-8 text-uppercase mb-1">Bài làm luận của Học viên (Số từ: {{ $writing->word_count ?? 0 }}):</label>
                                    <div class="p-3 border rounded bg-light text-dark fs-7 whitespace-pre-wrap shadow-inner mb-3" style="max-height: 250px; overflow-y: auto;">
                                        {{ $writing ? $writing->essay_content : 'Không nộp nội dung bài viết.' }}
                                    </div>
                                    
                                    @if($writing)
                                        <div class="row align-items-center bg-warning-subtle p-3 rounded border border-warning-subtle mx-0">
                                            <div class="col-md-7">
                                                <label class="fw-bold text-warning-heading fs-7 mb-1">Nhập số điểm cho câu Writing này (Thang điểm 9.0):</label>
                                                <small class="text-muted d-block fs-8">Ví dụ: 6.5, 7.0, 7.5...</small>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="number" step="0.25" min="0" max="9" 
                                                       name="writing_scores[{{ $writing->id }}]" 
                                                       value="{{ $writing->teacher_score }}" 
                                                       class="form-control border-warning bg-white fw-bold text-center text-arena fs-5" 
                                                       placeholder="0.0 - 9.0">
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            {{-- 4. CHẤM ĐIỂM SPEAKING --}}
                            @elseif($question->question_type === 'speaking')
                                @php $speaking = $speakingAnswers->get($question->id); @endphp
                                <div class="mt-2">
                                    <label class="fw-bold text-muted fs-8 text-uppercase mb-1">File ghi âm học viên nộp:</label>
                                    <div class="mb-3 p-2 border rounded bg-light">
                                        @if($speaking && $speaking->audio_file_path)
                                            <audio controls class="w-100" style="max-height: 40px;">
                                                <source src="{{ asset('storage/' . $speaking->audio_file_path) }}" type="audio/mpeg">
                                                Trình duyệt không hỗ trợ phát Audio này.
                                            </audio>
                                        @else
                                            <span class="text-danger fs-7 italic d-block py-1">Không tìm thấy file thu âm bài làm.</span>
                                        @endif
                                    </div>

                                    @if($speaking)
                                        <div class="row align-items-center bg-info-subtle p-3 rounded border border-info-subtle mx-0">
                                            <div class="col-md-7">
                                                <label class="fw-bold text-info-heading fs-7 mb-1">Nhập số điểm cho câu Speaking này (Thang điểm 9.0):</label>
                                                <small class="text-muted d-block fs-8">Ví dụ: 5.5, 6.0, 6.5...</small>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="number" step="0.25" min="0" max="9" 
                                                       name="speaking_scores[{{ $speaking->id }}]" 
                                                       value="{{ $speaking->teacher_score }}" 
                                                       class="form-control border-info bg-white fw-bold text-center text-arena fs-5" 
                                                       placeholder="0.0 - 9.0">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- CỘT PHẢI: Khối Panel tổng hợp kết quả tự động chấm & Lưu điểm cuối cùng --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-white rounded sticky-top" style="top: 20px; z-index: 10;">
                    <div class="card-header bg-white border-bottom text-dark fw-bold py-3 text-uppercase fs-7">
                        Điểm số thành phần tự động
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <small class="text-muted fw-bold d-block mb-1">LISTENING GRADE (Hệ thống)</small>
                            <h4 class="fw-bold text-success font-monospace">{{ $submission->listening_grade !== null ? number_format($submission->listening_grade, 2) : 'N/A' }}</h4>
                        </div>
                        <div class="mb-4">
                            <small class="text-muted fw-bold d-block mb-1">READING GRADE (Hệ thống)</small>
                            <h4 class="fw-bold text-success font-monospace">{{ $submission->reading_grade !== null ? number_format($submission->reading_grade, 2) : 'N/A' }}</h4>
                        </div>

                        <hr class="opacity-25">

                        {{-- Ô nhận xét của Giáo viên gửi học viên --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark fs-7">Nhận xét tổng quan của Giáo viên:</label>
                            <textarea name="teacher_comment" rows="4" class="form-control fs-7 bg-light-subtle" 
                                      placeholder="Viết lời khuyên, đánh giá điểm mạnh/yếu của học viên tại đây...">{{ $submission->teacher_comment }}</textarea>
                        </div>

                        {{-- Nút Submit xác nhận hoàn tất chấm bài (Được chuyển sang đỏ đô Arena) --}}
                        <button type="submit" class="btn btn-arena btn-lg w-100 fw-bold shadow-sm py-2.5 fs-6">
                            HOÀN TẤT CHẤM BÀI
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    :root {
        --arena-primary: #990000;
        --arena-hover: #770000;
    }
    /* Các class custom Arena đồng bộ */
    .text-arena { color: var(--arena-primary) !important; }
    .bg-arena { background-color: var(--arena-primary) !important; color: #fff !important; }
    .btn-arena { background-color: var(--arena-primary) !important; color: #fff !important; border: 1px solid var(--arena-primary); transition: all 0.2s ease-in-out; }
    .btn-arena:hover { background-color: var(--arena-hover) !important; border-color: var(--arena-hover) !important; color: #fff !important; }
</style>
@endsection