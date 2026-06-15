@extends('layouts.student_classroom')

@section('title', 'Đang làm bài tập / Đề thi')

@section('class_content')
<div class="container-fluid p-0">
    
    {{-- Thanh tiêu đề đầu bài & Bộ đếm thời gian cố định --}}
    <div class="card shadow-sm border-0 mb-4 sticky-top bg-white border-bottom py-2">
        <div class="card-body d-flex justify-content-between align-items-center py-2 px-4">
            <div>
                <span class="badge bg-danger mb-1">EXAM WORKING WORKSPACE</span>
                <h5 class="fw-bold m-0 text-dark">{{ $distribution->assignment->title }}</h5>
            </div>
            @if($distribution->duration_minutes)
                <div class="text-end">
                    <span class="fs-7 text-muted d-block fw-medium">THỜI GIAN CÒN LẠI</span>
                    <div id="countdown-timer" class="fs-4 fw-bold text-danger">--:--</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Form làm bài --}}
    <form id="exam-form" action="{{ route('student.classes.assignments.submit', [$class->id, $distribution->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($distribution->assignment->file_path)
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark"><i class="bi bi-headphones me-2"></i> FILE ÂM THANH BÀI THI:</span>
                <audio controls class="ms-3 flex-grow-1" style="max-height: 40px;">
                    <source src="{{ asset('storage/' . $distribution->assignment->file_path) }}" type="audio/mpeg">
                    Trình duyệt không hỗ trợ nghe Audio.
                </audio>
            </div>
        </div>
        @endif
        <div class="row g-4">
            {{-- Danh sách câu hỏi bên trái --}}
            <div class="col-lg-9">
                @foreach($distribution->assignment->questions as $index => $question)
                    <div class="card shadow-sm border-0 mb-4 rounded-3 card-question" id="q-box-{{ $question->id }}">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start mb-3">
                                <span class="badge bg-dark me-2 px-2 py-2 fs-7">Câu {{ $question->question_number }}</span>
                                <div class="fw-semibold fs-6 text-secondary pt-1">
                                    @if($question->skill_id == 1) <span class="text-primary">[Listening]</span>
                                    @elseif($question->skill_id == 2) <span class="text-success">[Reading]</span>
                                    @elseif($question->skill_id == 3) <span class="text-info">[Writing]</span>
                                    @elseif($question->skill_id == 4) <span class="text-warning">[Speaking]</span>
                                    @endif
                                </div>
                            </div>

                            <div class="question-text text-dark mb-4 fs-6">
                                {!! nl2br(e($question->question_text)) !!}
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- HIỂN THỊ THEO TỪNG THỂ LOẠI CÂU HỎI --}}
                            
                            {{-- 1. Trắc nghiệm --}}
                            @if($question->question_type === 'trac_nghiem')
                                <div class="options-group d-flex flex-column gap-2">
                                    @foreach($question->options as $option)
                                        <label class="d-flex align-items-center p-3 rounded border border-light-subtle bg-light-subtle cursor-pointer transition-all option-label">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" class="question-radio" data-qid="{{ $question->id }}">
                                            <span class="fw-bold me-2">{{ $option->option_letter }}.</span> {{ $option->option_content }}
                                        </label>
                                    @endforeach
                                </div>

                            {{-- 2. Điền từ --}}
                            @elseif($question->question_type === 'dien_tu')
                                <div class="row g-3 bg-light p-3 rounded">
                                    <p class="fs-7 text-muted fw-medium m-0"><i class="bi bi-pencil-square"></i> Nhập từ khóa tương ứng với các ô trống:</p>
                                    @foreach($question->keywords as $keyword)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="input-group input-group-sm">
                                                <span class="input-grouptext bg-white fw-bold">Ô số {{ $keyword->blank_order }}</span>
                                                <input type="text" 
                                                    name="answers_blank[{{ $question->id }}][{{ $keyword->blank_order }}]" 
                                                    class="form-control" 
                                                    placeholder="Nhập đáp án...">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            {{-- 3. Viết luận --}}
                            @elseif($question->question_type === 'writing')
                                <div class="form-group">
                                    <textarea name="answers_writing[{{ $question->id }}]" rows="10" class="form-control question-textarea" data-qid="{{ $question->id }}" placeholder="Viết bài luận của bạn tại đây..."></textarea>
                                    <div class="text-end fs-7 text-muted mt-2 fw-medium">
                                        Số từ tạm tính: <span id="word-count-{{ $question->id }}">0</span> từ
                                    </div>
                                </div>

                            {{-- 4. Nói / Ghi âm --}}
                            @elseif($question->question_type === 'speaking') 
                                <div class="speaking-container bg-light p-3 rounded border mb-3" data-qid="{{ $question->id }}">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="btn-start-{{ $question->id }}" onclick="startLiveRecording({{ $question->id }})">
                                            <i class="bi bi-mic-fill"></i> Bắt đầu ghi âm trực tiếp
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm d-none" id="btn-stop-{{ $question->id }}" onclick="stopLiveRecording({{ $question->id }})">
                                            <i class="bi bi-stop-fill"></i> Dừng và Lưu đoạn ghi
                                        </button>
                                        <span class="text-danger fw-bold d-none text-blink" id="status-recording-{{ $question->id }}">
                                            <i class="bi bi-record-circle-fill text-danger"></i> Đang thu âm...
                                        </span>
                                    </div>

                                    <div class="mb-3 d-none" id="preview-container-{{ $question->id }}">
                                        <label class="fs-7 text-success d-block fw-semibold mb-1">Bản nghe thử của bạn:</label>
                                        <audio id="audio-playback-{{ $question->id }}" controls class="w-100"></audio>
                                    </div>

                                    <div class="form-group">
                                        <label class="fs-7 text-muted mb-1">Hoặc chọn File âm thanh từ thiết bị của bạn:</label>
                                        <input type="file" name="answers_speaking[{{ $question->id }}]" id="speaking-file-{{ $question->id }}" class="form-control question-file" accept="audio/*">
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Thanh điều hướng câu hỏi bên phải (Sidebar) --}}
            <div class="col-lg-3">
                <div class="card shadow-sm border-0 sticky-top" style="top: 100px; z-index: 99;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 fw-bold text-dark text-uppercase fs-7"><i class="bi bi-grid-3x3-gap-fill me-1"></i> Bảng tiến độ câu hỏi</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap gap-2 mb-4 justify-content-start">
                            @foreach($distribution->assignment->questions as $question)
                                <a href="#q-box-{{ $question->id }}" id="nav-q-{{ $question->id }}" class="btn btn-sm btn-outline-secondary fw-semibold rounded-2 d-flex align-items-center justify-content-center btn-nav-question" style="width: 40px; height: 40px;">
                                    {{ $question->question_number }}
                                </a>
                            @endforeach
                        </div>
                        
                        <hr class="opacity-25 text-muted">
                        
                        <form action="{{ route('student.classes.assignments.submit', [$class->id, $distribution->id]) }}" method="POST">
                            @csrf
                            <button type="submit">Nộp bài</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .option-label:hover { background-color: #f1f3f5 !important; }
    .btn-nav-active { background-color: #495057 !important; color: #fff !important; border-color: #495057 !important; }
</style>

{{-- JAVASCRIPT XỬ LÝ BỘ ĐẾM THỜI GIAN & ĐÁNH DẤU CÂU ĐÃ LÀM --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. Logic Bộ Đếm Ngược ---
    @if($distribution->duration_minutes)
        let durationSeconds = {{ $distribution->duration_minutes * 60 }};
        const timerDisplay = document.getElementById('countdown-timer');
        const examForm = document.getElementById('exam-form');

        const timer = setInterval(function() {
            let minutes = Math.floor(durationSeconds / 60);
            let seconds = durationSeconds % 60;

            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            timerDisplay.textContent = minutes + ':' + seconds;

            if (durationSeconds <= 0) {
                clearInterval(timer);
                alert('Hết giờ làm bài! Hệ thống tự động nộp bài của bạn.');
                examForm.submit(); // Tự động submit
            }
            durationSeconds--;
        }, 1000);
    @endif

    // --- 2. Đánh dấu Tiến Độ Câu Hỏi (Highlight Ô câu hỏi đã điền/chọn đáp án) ---
    function markActiveNav(qId) {
        const navBtn = document.getElementById('nav-q-' + qId);
        if (navBtn) navBtn.classList.add('btn-nav-active');
    }

    // Trắc nghiệm radio
    document.querySelectorAll('.question-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            markActiveNav(this.getAttribute('data-qid'));
        });
    });

    // Điền từ (kiểm tra xem tất cả các ô trong cùng 1 câu đã có chữ chưa)
    document.querySelectorAll('.question-input').forEach(function(input) {
        input.addEventListener('input', function() {
            markActiveNav(this.getAttribute('data-qid'));
        });
    });

    // Văn bản luận & Đếm số từ thực tế
    document.querySelectorAll('.question-textarea').forEach(function(textarea) {
        textarea.addEventListener('input', function() {
            const qId = this.getAttribute('data-qid');
            markActiveNav(qId);
            
            // Xử lý đếm từ sơ bộ
            const text = this.value.trim();
            const words = text ? text.split(/\s+/).length : 0;
            document.getElementById('word-count-' + qId).textContent = words;
        });
    });

    // Tải file audio nói
    document.querySelectorAll('.question-file').forEach(function(fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                markActiveNav(this.getAttribute('data-qid'));
            }
        });
    });
});
let recorders = {};
let recordingChunks = {};

function startLiveRecording(qId) {
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            recordingChunks[qId] = [];
            recorders[qId] = new MediaRecorder(stream);

            recorders[qId].addEventListener("dataavailable", event => {
                if (event.data.size > 0) recordingChunks[qId].push(event.data);
            });

            recorders[qId].addEventListener("stop", () => {
                const audioBlob = new Blob(recordingChunks[qId], { type: 'audio/mp3' });
                const audioUrl = URL.createObjectURL(audioBlob);
                
                // Hiển thị khung nghe thử
                document.getElementById(`audio-playback-${qId}`).src = audioUrl;
                document.getElementById(`preview-container-${qId}`).classList.remove('d-none');

                // Chuyển dữ liệu Blob vừa thu âm trực tiếp thành một File thật sự đưa vào Input File
                const recordedFile = new File([audioBlob], `browser_record_${qId}.mp3`, { type: "audio/mp3" });
                const container = new DataTransfer();
                container.items.add(recordedFile);
                document.getElementById(`speaking-file-${qId}`).files = container.files;

                // Đồng bộ cập nhật trạng thái làm bài lên thanh sidebar điều hướng câu hỏi
                markActiveNav(qId);
                
                // Giải phóng microphone thiết bị
                stream.getTracks().forEach(track => track.stop());
            });

            recorders[qId].start();
            document.getElementById(`btn-start-${qId}`).classList.add('d-none');
            document.getElementById(`btn-stop-${qId}`).classList.remove('d-none');
            document.getElementById(`status-recording-${qId}`).classList.remove('d-none');
        })
        .catch(err => {
            alert('Lỗi truy cập Microphone trình duyệt: ' + err.message);
        });
}

function stopLiveRecording(qId) {
    if (recorders[qId] && recorders[qId].state !== "inactive") {
        recorders[qId].stop();
        document.getElementById(`btn-start-${qId}`).classList.remove('d-none');
        document.getElementById(`btn-stop-${qId}`).classList.add('d-none');
        document.getElementById(`status-recording-${qId}`).classList.add('d-none');
    }
}
</script>
@endsection