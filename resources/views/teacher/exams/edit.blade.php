@extends('layouts.teacher')

@section('title', 'Cập nhật cấu hình Đề thi')

@section('teacher_content')
<div class="container py-4" style="max-width: 1000px;">
    <div class="mb-3">
        <a href="{{ route('teacher.exams.index') }}" class="text-decoration-none text-secondary"><i class="bi bi-x-circle"></i> Hủy và quay lại</a>
    </div>

    <form id="exam-form" action="{{ route('teacher.exams.update', $exam->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: #990000;">CẬP NHẬT CẤU HÌNH ĐỀ THI GỐC</h5>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Tiêu đề đề thi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" value="{{ $exam->title }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phân loại Đề <span class="text-danger">*</span></label>
                        <select class="form-select" name="assignment_type_id" required>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ $exam->assignment_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Tệp âm thanh đính kèm (Dành cho bài Listening)</label>
                        <input type="file" class="form-control" name="file_path" accept="audio/*">
                        <small class="text-muted d-block mt-1">Hệ thống chỉ chấp nhận định dạng âm thanh (.mp3, .wav, .m4a, .wma) phục vụ làm bài thi nghe.</small>
                        @if($exam->file_path)
                            <small class="text-success d-block mt-1">Đề thi hiện đang có file đính kèm lưu trên hệ thống.</small>
                        @endif
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Hướng dẫn làm bài</label>
                        <textarea class="form-control" name="description" rows="2">{{ $exam->description }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3 text-dark">DANH SÁCH CÂU HỎI HIỆN TẠI</h5>
        <div class="alert alert-warning py-2 small shadow-sm"><i class="bi bi-info-circle-fill"></i> Mẹo: Toàn bộ cấu trúc câu hỏi sẽ được ghi đè đồng bộ phiên bản mới nhất.</div>

        <div id="questions-container">
            @foreach($exam->questions as $index => $question)
                @php $qIndex = $index + 1; @endphp
                <div class="card shadow-sm border-0 mb-3 question-card" id="q-card-{{ $qIndex }}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-secondary">Câu hỏi số #{{ $qIndex }}</span>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="questions[{{ $qIndex }}][question_number]" value="{{ $qIndex }}">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Dạng câu hỏi</label>
                                <select name="questions[{{ $qIndex }}][question_type]" class="form-select form-select-sm" readonly style="pointer-events: none; background-color: #e9ecef;">
                                    <option value="trac_nghiem" {{ $question->question_type == 'trac_nghiem' ? 'selected' : '' }}>Trắc nghiệm</option>
                                    <option value="dien_tu" {{ $question->question_type == 'dien_tu' ? 'selected' : '' }}>Điền từ vào ô trống</option>
                                    <option value="writing" {{ $question->question_type == 'writing' ? 'selected' : '' }}>Writing</option>
                                    <option value="speaking" {{ $question->question_type == 'speaking' ? 'selected' : '' }}>Speaking</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Kỹ năng tương ứng</label>
                                <select name="questions[{{ $qIndex }}][skill_id]" class="form-select form-select-sm" required>
                                    @foreach($skills as $skill)
                                        <option value="{{ $skill->id }}" {{ $question->skill_id == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="hidden" name="questions[{{ $qIndex }}][points]" value="1">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Nội dung câu hỏi / Yêu cầu đề</label>
                                <textarea name="questions[{{ $qIndex }}][question_text]" class="form-control form-control-sm" rows="2" required>{{ $question->question_text }}</textarea>
                            </div>
                        </div>

                        <div id="dynamic-content-{{ $qIndex }}">
                            @if($question->question_type === 'trac_nghiem')
                                <div class="p-3 bg-light border rounded">
                                    <div class="row g-2">
                                        @foreach($question->options->sortBy('option_letter') as $oKey => $opt)
                                            <div class="col-md-6 d-flex align-items-center gap-2">
                                                <span class="fw-bold">{{ $opt->option_letter }}.</span>
                                                <input type="hidden" name="questions[{{ $qIndex }}][options][{{ $oKey }}][option_letter]" value="{{ $opt->option_letter }}">
                                                <input type="text" name="questions[{{ $qIndex }}][options][{{ $oKey }}][option_content]" class="form-control form-control-sm" value="{{ $opt->option_content }}" required>
                                                <div class="form-check m-0">
                                                    <input class="form-check-input" type="radio" name="questions[{{ $qIndex }}][correct_option]" value="{{ $opt->option_letter }}" {{ $opt->is_correct ? 'checked' : '' }} required>
                                                    <label class="form-check-label small">Đúng</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($question->question_type === 'dien_tu')
                                <div class="p-3 bg-light border rounded">
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        @foreach($question->keywords as $kKey => $keyword)
                                            <div class="d-flex align-items-center gap-1 border p-1 rounded bg-white">
                                                <span class="badge bg-secondary">Ô số {{ $keyword->blank_order }}</span>
                                                <input type="hidden" name="questions[{{ $qIndex }}][keywords][{{ $kKey }}][blank_order]" value="{{ $keyword->blank_order }}">
                                                <input type="text" name="questions[{{ $qIndex }}][keywords][{{ $kKey }}][correct_keyword]" class="form-control form-control-sm border-0" value="{{ $keyword->correct_keyword }}" style="width:140px;" required>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($question->question_type === 'speaking')
                                <div class="p-3 bg-light border rounded">
                                    <label class="form-label small fw-bold text-danger">Giới hạn thời gian ghi âm (giây)</label>
                                    <input type="number" name="questions[{{ $qIndex }}][max_recording_time]" class="form-control form-control-sm" style="width:150px;" value="{{ $question->max_recording_time ?? 120 }}">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn text-white fw-bold px-5 py-2 shadow-sm" style="background-color: #990000;">
                <i class="bi bi-check-circle-fill me-1"></i> LƯU CẬP NHẬT ĐỀ THI
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('exam-form').addEventListener('submit', function(event) {
    const selectedSkillIds = new Set(
        Array.from(this.querySelectorAll('[name$="[skill_id]"]'))
            .map((input) => input.value)
            .filter(Boolean)
    );

    if (selectedSkillIds.size < 4) {
        const confirmed = confirm('Đề thi này chưa đủ 4 kỹ năng. Hệ thống sẽ chỉ chấm điểm và tính overall trên các kỹ năng có trong đề. Bạn có muốn tiếp tục lưu không?');

        if (!confirmed) {
            event.preventDefault();
        }
    }
});
</script>
@endsection
