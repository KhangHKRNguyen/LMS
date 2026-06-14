@extends('layouts.teacher')

@section('title', 'Thiết lập Đề thi Mới')

@section('teacher_content')
<div class="container py-4" style="max-width: 1000px;">
    <div class="mb-3">
        <a href="{{ route('teacher.exams.index') }}" class="text-decoration-none text-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <form action="{{ route('teacher.exams.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: #990000;">THIẾT LẬP THÔNG TIN ĐỀ THI GỐC</h5>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Tiêu đề đề thi / Bài kiểm tra <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required placeholder="Ví dụ: IELTS Mock Test Oct 2026">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phân loại Đề <span class="text-danger">*</span></label>
                        <select class="form-select" name="assignment_type_id" required>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Tài liệu đính kèm đề bài (PDF, Audio, File Đề gốc nếu có)</label>
                        <input type="file" class="form-control" name="file_path">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Hướng dẫn làm bài / Mô tả tổng quan</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3 text-dark d-flex justify-content-between align-items-center">
            <span>DANH SÁCH CÂU HỎI CHI TIẾT</span>
            <button type="button" id="add-question-btn" class="btn btn-sm text-white px-3" style="background-color: #800000;">
                <i class="bi bi-plus-circle me-1"></i> THÊM CÂU HỎI
            </button>
        </h5>

        <div id="questions-container">
            </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn text-white fw-bold px-5 py-2 shadow-sm" style="background-color: #990000;">
                <i class="bi bi-cloud-arrow-up me-1"></i> LƯU ĐỀ THI VÀO NGÂN HÀNG
            </button>
        </div>
    </form>
</div>

<script>
let questionCount = 0;

document.getElementById('add-question-btn').addEventListener('click', function() {
    questionCount++;
    const container = document.getElementById('questions-container');
    
    const questionHtml = `
        <div class="card shadow-sm border-0 mb-3 question-card" id="q-card-${questionCount}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold text-secondary">Câu hỏi số #${questionCount}</span>
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeQuestion(${questionCount})">Xóa câu này</button>
            </div>
            <div class="card-body">
                <input type="hidden" name="questions[${questionCount}][question_number]" value="${questionCount}">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Dạng câu hỏi <span class="text-danger">*</span></label>
                        <select name="questions[${questionCount}][question_type]" class="form-select form-select-sm" onchange="toggleQuestionType(this, ${questionCount})" required>
                            <option value="trac_nghiem">Trắc nghiệm (Multiple Choice)</option>
                            <option value="dien_tu">Điền từ vào ô trống (Fill Blank)</option>
                            <option value="writing">Tự luận / Viết (Writing)</option>
                            <option value="speaking">Ghi âm / Nói (Speaking)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Kỹ năng tương ứng <span class="text-danger">*</span></label>
                        <select name="questions[${questionCount}][skill_id]" class="form-select form-select-sm" required>
                            @foreach($skills as $skill)
                                <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Điểm số <span class="text-danger">*</span></label>
                        <input type="number" step="0.25" name="questions[${questionCount}][points]" class="form-control form-control-sm" value="1.00" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Nội dung câu hỏi / Đoạn văn / Yêu cầu đề</label>
                        <textarea name="questions[${questionCount}][question_text]" class="form-control form-control-sm" rows="2" placeholder="Nhập nội dung câu hỏi tại đây..."></textarea>
                    </div>
                </div>

                <div id="dynamic-content-${questionCount}">
                    ${renderMultipleChoiceTemplate(questionCount)}
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', questionHtml);
});

function removeQuestion(id) {
    document.getElementById(`q-card-${id}`).remove();
}

function toggleQuestionType(selectElement, id) {
    const type = selectElement.value;
    const block = document.getElementById(`dynamic-content-${id}`);
    
    if (type === 'trac_nghiem') {
        block.innerHTML = renderMultipleChoiceTemplate(id);
    } else if (type === 'dien_tu') {
        block.innerHTML = renderFillBlankTemplate(id);
    } else if (type === 'speaking') {
        block.innerHTML = `
            <div class="p-3 bg-light border rounded">
                <label class="form-label small fw-bold text-danger">Giới hạn thời gian ghi âm tối đa (giây)</label>
                <input type="number" name="questions[${id}][max_recording_time]" class="form-control form-control-sm" style="width:150px;" value="120" placeholder="Ví dụ: 120">
            </div>
        `;
    } else {
        block.innerHTML = `<p class="text-muted small m-0"><i class="bi bi-info-circle"></i> Dạng câu hỏi viết luận (Writing) học viên sẽ có khung soạn thảo văn bản riêng khi làm bài.</p>`;
    }
}

function renderMultipleChoiceTemplate(id) {
    return `
        <div class="p-3 bg-light border rounded">
            <span class="d-block small fw-bold mb-2 text-primary">Thiết lập các phương án lựa chọn và đáp án đúng:</span>
            <div class="row g-2">
                ${['A', 'B', 'C', 'D'].map(letter => `
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <span class="fw-bold">${letter}.</span>
                        <input type="hidden" name="questions[${id}][options][${letter}][option_letter]" value="${letter}">
                        <input type="text" name="questions[${id}][options][${letter}][option_content]" class="form-control form-control-sm" placeholder="Nội dung phương án ${letter}" required>
                        <div class="form-check m-0">
                            <input class="form-check-input" type="radio" name="questions[${id}][options_correct]" value="${letter}" onchange="updateCorrectRadio(${id}, '${letter}')">
                            <input type="hidden" id="correct-input-${id}-${letter}" name="questions[${id}][options][${letter}][is_correct]" value="0">
                            <label class="form-check-label small">Đúng</label>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function updateCorrectRadio(id, correctLetter) {
    ['A', 'B', 'C', 'D'].forEach(letter => {
        document.getElementById(`correct-input-${id}-${letter}`).value = (letter === correctLetter) ? "1" : "0";
    });
}

function renderFillBlankTemplate(id) {
    return `
        <div class="p-3 bg-light border rounded">
            <span class="d-block small fw-bold mb-2 text-success">Thiết lập từ khóa đáp án chuẩn (Mỗi từ khóa tương ứng với 1 ô trống xếp thứ tự):</span>
            <div class="d-flex flex-wrap gap-2 align-items-center" id="blank-wrapper-${id}">
                <div class="d-flex align-items-center gap-1 border p-1 rounded bg-white">
                    <span class="badge bg-secondary">Ô số 1</span>
                    <input type="hidden" name="questions[${id}][keywords][0][blank_order]" value="1">
                    <input type="text" name="questions[${id}][keywords][0][correct_keyword]" class="form-control form-control-sm border-0" placeholder="Từ khóa đúng" style="width:140px;" required>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" onclick="addBlankField(${id})">+ Thêm ô trống tiếp theo</button>
            </div>
        </div>
    `;
}

function addBlankField(id) {
    const wrapper = document.getElementById(`blank-wrapper-${id}`);
    const currentBlanks = wrapper.getElementsByClassName('badge').length;
    const nextOrder = currentBlanks + 1;
    
    const fieldHtml = `
        <div class="d-flex align-items-center gap-1 border p-1 rounded bg-white">
            <span class="badge bg-secondary">Ô số ${nextOrder}</span>
            <input type="hidden" name="questions[${id}][keywords][${currentBlanks}][blank_order]" value="${nextOrder}">
            <input type="text" name="questions[${id}][keywords][${currentBlanks}][correct_keyword]" class="form-control form-control-sm border-0" placeholder="Từ khóa đúng" style="width:140px;" required>
        </div>
    `;
    wrapper.insertAdjacentHTML('afterbegin', fieldHtml);
}
</script>
@endsection