@extends('layouts.teacher')

@section('title', 'Thiết lập Đề thi Mới')

@section('teacher_content')
<div class="container py-4" style="max-width: 1000px;">
    <div class="mb-3">
        <a href="{{ route('teacher.exams.index') }}" class="text-decoration-none text-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <form id="exam-form" action="{{ route('teacher.exams.store') }}" method="POST" enctype="multipart/form-data">
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
                        <label class="form-label fw-semibold">Tệp âm thanh đính kèm (Dành cho bài Listening)</label>
                        <input type="file" class="form-control" name="file_path" accept="audio/*">
                        <small class="text-muted d-block mt-1">Hệ thống chỉ chấp nhận định dạng âm thanh (.mp3, .wav, .m4a, .wma) phục vụ làm bài thi nghe.</small>
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

        <div id="questions-container"></div>

        <div class="text-center mt-4">
            <button type="submit" class="btn text-white fw-bold px-5 py-2 shadow-sm" style="background-color: #990000;">
                <i class="bi bi-cloud-arrow-up me-1"></i> LƯU ĐỀ THI VÀO NGÂN HÀNG
            </button>
        </div>
    </form>
</div>

<script>
let questionCount = 0;

function addQuestion() {
    questionCount++;
    const wrapper = document.getElementById('questions-container');
    
    const cardHtml = `
        <div class="card shadow-sm border-0 mb-3 question-card" id="question-card-${questionCount}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold text-dark fs-6">Câu hỏi số <span class="q-number">${questionCount}</span></span>
                <input type="hidden" name="questions[${questionCount}][question_number]" value="${questionCount}" class="q-number-input">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeQuestion(${questionCount})">
                    <i class="bi bi-trash"></i> Xóa câu hỏi
                </button>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Loại câu hỏi</label>
                        <select class="form-select form-select-sm" name="questions[${questionCount}][question_type]" onchange="handleQuestionTypeChange(${questionCount}, this.value)">
                            <option value="trac_nghiem">Trắc nghiệm (Linh hoạt số đáp án)</option>
                            <option value="dien_tu">Điền từ vào ô trống</option>
                            <option value="writing">Writing (Tự luận viết)</option>
                            <option value="speaking">Speaking (Ghi âm nói)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kỹ năng tương ứng</label>
                        <select class="form-select form-select-sm skill-select-field" name="questions[${questionCount}][skill_id]" id="skill-select-${questionCount}">
                            @foreach($skills as $skill)
                                <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                            @endforeach
                        </select>
                        <div id="hidden-skill-container-${questionCount}"></div>
                    </div>
                    <input type="hidden" name="questions[${questionCount}][points]" value="1">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nội dung câu hỏi / Yêu cầu đề bài</label>
                    <textarea class="form-control form-control-sm" name="questions[${questionCount}][question_text]" rows="2" placeholder="Nhập nội dung đề bài tại đây..." required></textarea>
                </div>

                <div id="dynamic-content-${questionCount}" class="p-3 bg-light rounded border border-dashed">
                    ${generateMultipleChoiceTemplate(questionCount)}
                </div>
            </div>
        </div>
    `;

    wrapper.insertAdjacentHTML('beforeend', cardHtml);
    updateGlobalQuestionNumbers();
}

function removeQuestion(id) {
    const card = document.getElementById(`question-card-${id}`);
    if (card) {
        card.remove();
        updateGlobalQuestionNumbers();
    }
}

function updateGlobalQuestionNumbers() {
    const cards = document.querySelectorAll('.question-card');
    cards.forEach((card, idx) => {
        const currentNum = idx + 1;
        card.querySelector('.q-number').textContent = currentNum;
        card.querySelector('.q-number-input').value = currentNum;
    });
}

function handleQuestionTypeChange(qId, typeValue) {
    const container = document.getElementById(`dynamic-content-${qId}`);
    const skillSelect = document.getElementById(`skill-select-${qId}`);
    const hiddenContainer = document.getElementById(`hidden-skill-container-${qId}`);
    
    if (!container || !skillSelect) return;
    hiddenContainer.innerHTML = '';

    if (typeValue === 'writing') {
        for (let option of skillSelect.options) {
            if (option.text.toLowerCase().includes('viết') || option.text.toLowerCase().includes('writing')) {
                skillSelect.value = option.value;
                break;
            }
        }
        skillSelect.disabled = true;
        skillSelect.removeAttribute('name');
        hiddenContainer.innerHTML = `<input type="hidden" name="questions[${qId}][skill_id]" value="${skillSelect.value}">`;
        container.innerHTML = `<div class="text-muted small"><i class="bi bi-info-circle me-1"></i> Định dạng tự luận viết (Writing). Học viên sẽ được cung cấp một ô soạn thảo văn bản lớn để làm bài.</div>`;

    } else if (typeValue === 'speaking') {
        for (let option of skillSelect.options) {
            if (option.text.toLowerCase().includes('nói') || option.text.toLowerCase().includes('speaking')) {
                skillSelect.value = option.value;
                break;
            }
        }
        skillSelect.disabled = true;
        skillSelect.removeAttribute('name');
        hiddenContainer.innerHTML = `<input type="hidden" name="questions[${qId}][skill_id]" value="${skillSelect.value}">`;
        container.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="form-label small fw-bold text-danger mb-0">Thời gian ghi âm tối đa (giây):</label>
                </div>
                <div class="col-auto">
                    <input type="number" name="questions[${qId}][max_recording_time]" class="form-control form-control-sm" style="width:100px;" value="120" min="10" required>
                </div>
            </div>
        `;
    } else {
        skillSelect.disabled = false;
        skillSelect.setAttribute('name', `questions[${qId}][skill_id]`);

        if (typeValue === 'trac_nghiem') {
            container.innerHTML = generateMultipleChoiceTemplate(qId);
        } else if (typeValue === 'dien_tu') {
            container.innerHTML = generateFillBlankTemplate(qId);
        }
    }
}

function generateMultipleChoiceTemplate(qId) {
    return `
        <div class="mb-2 fw-semibold small text-secondary">Thiết lập các phương án đáp án:</div>
        <div id="options-wrapper-${qId}" class="d-flex flex-column gap-2 mb-2">
            ${renderOptionRow(qId, 0, 'A')}
            ${renderOptionRow(qId, 1, 'B')}
            ${renderOptionRow(qId, 2, 'C')}
            ${renderOptionRow(qId, 3, 'D')}
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" onclick="addOptionField(${qId})">
            <i class="bi bi-plus-circle me-1"></i> Thêm đáp án lựa chọn
        </button>
    `;
}

function renderOptionRow(qId, oIdx, letter) {
    return `
        <div class="d-flex align-items-center gap-2 option-row-${qId}">
            <div class="form-check m-0">
                <input class="form-check-input mt-1 question-radio-${qId}" type="radio" name="questions[${qId}][correct_option]" value="${letter}" required>
            </div>
            <span class="fw-bold text-dark font-monospace option-letter-${qId}" style="width: 20px;">${letter}</span>
            <input type="hidden" name="questions[${qId}][options][${oIdx}][option_letter]" value="${letter}" class="option-letter-hidden-${qId}">
            <input type="text" name="questions[${qId}][options][${oIdx}][option_content]" class="form-control form-control-sm" placeholder="Nhập đáp án..." required>
            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 btn-delete-option-${qId}" onclick="removeOptionField(this, ${qId})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

function addOptionField(qId) {
    const wrapper = document.getElementById(`options-wrapper-${qId}`);
    const currentRows = wrapper.getElementsByClassName(`option-row-${qId}`).length;
    if(currentRows >= 26) return; // Giới hạn bảng chữ cái từ A-Z
    const letter = String.fromCharCode(65 + currentRows);
    
    const div = document.createElement('div');
    div.innerHTML = renderOptionRow(qId, currentRows, letter);
    wrapper.appendChild(div.firstElementChild);
    updateOptionIndexes(qId);
}

function removeOptionField(btn, qId) {
    const row = btn.closest(`.option-row-${qId}`);
    if (row) {
        row.remove();
        updateOptionIndexes(qId);
    }
}

function updateOptionIndexes(qId) {
    const wrapper = document.getElementById(`options-wrapper-${qId}`);
    if (!wrapper) return;
    const rows = wrapper.getElementsByClassName(`option-row-${qId}`);
    
    Array.from(rows).forEach((row, idx) => {
        const letter = String.fromCharCode(65 + idx);
        row.querySelector(`.option-letter-${qId}`).textContent = letter;
        
        const hiddenLetter = row.querySelector(`.option-letter-hidden-${qId}`);
        hiddenLetter.value = letter;
        hiddenLetter.name = `questions[${qId}][options][${idx}][option_letter]`;
        
        const contentInput = row.querySelector('input[type="text"]');
        contentInput.name = `questions[${qId}][options][${idx}][option_content]`;
        
        const radioInput = row.querySelector(`.question-radio-${qId}`);
        radioInput.name = `questions[${qId}][correct_option]`;
        
        const oldChecked = radioInput.checked;
        radioInput.value = letter;
        if(oldChecked) radioInput.checked = true;
        
        const deleteBtn = row.querySelector(`.btn-delete-option-${qId}`);
        if (rows.length <= 2) {
            deleteBtn.style.setProperty('display', 'none', 'important');
        } else {
            deleteBtn.style.setProperty('display', 'block', 'important');
        }
    });
}

function generateFillBlankTemplate(id) {
    return `
        <div class="mb-2 fw-semibold small text-secondary">Thiết lập từ khóa cho các ô trống (Theo thứ tự từ trái qua phải):</div>
        <div class="d-flex flex-wrap gap-2 align-items-center" id="blank-wrapper-${id}">
            <div class="d-flex align-items-center gap-1 border p-1 rounded bg-white">
                <span class="badge bg-secondary">Ô số 1</span>
                <input type="hidden" name="questions[id][keywords][0][blank_order]" value="1">
                <input type="text" name="questions[${id}][keywords][0][correct_keyword]" class="form-control form-control-sm border-0" placeholder="Từ khóa đúng" style="width:140px;" required>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" onclick="addBlankField(${id})">+ Thêm ô trống tiếp theo</button>
        </div>
    `;
}

function addBlankField(id) {
    const wrapper = document.getElementById(`blank-wrapper-${id}`);
    const btn = wrapper.querySelector('button');
    const currentBlanks = wrapper.getElementsByClassName('badge').length;
    const nextOrder = currentBlanks + 1;
    
    const fieldHtml = `
        <div class="d-flex align-items-center gap-1 border p-1 rounded bg-white">
            <span class="badge bg-secondary">Ô số ${nextOrder}</span>
            <input type="hidden" name="questions[${id}][keywords][${currentBlanks}][blank_order]" value="${nextOrder}">
            <input type="text" name="questions[${id}][keywords][${currentBlanks}][correct_keyword]" class="form-control form-control-sm border-0" placeholder="Từ khóa đúng" style="width:140px;" required>
            <button type="button" class="btn-close ms-1" style="font-size:10px;" onclick="this.parentElement.remove(); reindexBlankFields(${id});"></button>
        </div>
    `;
    btn.insertAdjacentHTML('beforebegin', fieldHtml);
}

function reindexBlankFields(id) {
    const wrapper = document.getElementById(`blank-wrapper-${id}`);
    const items = wrapper.querySelectorAll('.border');
    items.forEach((item, idx) => {
        const nextOrder = idx + 1;
        item.querySelector('.badge').textContent = `Ô số ${nextOrder}`;
        item.querySelector('input[type="hidden"]').value = nextOrder;
        item.querySelector('input[type="hidden"]').name = `questions[${id}][keywords][${idx}][blank_order]`;
        item.querySelector('input[type="text"]').name = `questions[${id}][keywords][${idx}][correct_keyword]`;
    });
}

document.getElementById('add-question-btn').addEventListener('click', addQuestion);

document.addEventListener("DOMContentLoaded", function() {
    if(questionCount === 0) {
        addQuestion();
    }
});

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
