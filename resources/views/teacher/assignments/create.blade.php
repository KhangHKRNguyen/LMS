@extends('layouts.teacher')

@section('title', 'Tạo Đề Thi Mới')

@section('teacher_content')
<div class="mb-4">
    <a href="{{ route('teacher.assignments.index') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại Ngân hàng đề
    </a>
</div>

<div class="card shadow-sm border-0 mx-auto" style="max-width: 850px; border-radius: 8px;">
    <div class="card-body p-5">
        <h4 class="text-center fw-bold mb-5" style="color: var(--primary-color, #990000); letter-spacing: 0.5px;">THIẾT LẬP ĐỀ THI MỚI (Lưu vào Ngân hàng)</h4>

        <form action="{{ route('teacher.assignments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row g-4">
                {{-- Tiêu đề đề thi --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Tiêu đề bài tập / Đề thi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" placeholder="Ví dụ: Đề thi IELTS Listening Test 1" style="height: 44px; border-radius: 6px;" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Hình thức & Kỹ năng --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Hình thức làm bài <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" name="type" id="assignment_type" style="height: 44px;" required>
                        <option value="Trắc nghiệm" {{ old('type') == 'Trắc nghiệm' ? 'selected' : '' }}>Trắc nghiệm</option>
                        <option value="Tự luận" {{ old('type') == 'Tự luận' ? 'selected' : '' }}>Tự luận</option>
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Kỹ năng đánh giá <span class="text-danger">*</span></label>
                    <select class="form-select @error('skill') is-invalid @enderror" name="skill" id="skill_select" style="height: 44px;" required>
                        <option value="Nghe" {{ old('skill') == 'Nghe' ? 'selected' : '' }}>Listening (Nghe)</option>
                        <option value="Đọc" {{ old('skill') == 'Đọc' ? 'selected' : '' }}>Reading (Đọc)</option>
                        <option value="Viết" {{ old('skill') == 'Viết' ? 'selected' : '' }}>Writing (Viết)</option>
                        <option value="Nói" {{ old('skill') == 'Nói' ? 'selected' : '' }}>Speaking (Nói)</option>
                    </select>
                    @error('skill') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Thời gian cấu hình cấu trúc đề --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Thời lượng thi (Phút) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" placeholder="Ví dụ: 60" style="height: 44px;" required>
                    @error('duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Khối động riêng biệt cho kỹ năng Nghe (Upload file audio) --}}
                <div class="col-md-12 d-none" id="audio_file_block">
                    <label class="form-label fw-semibold text-success"><i class="bi bi-music-note-beamed"></i> File âm thanh bài nghe (.mp3, .wav) <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('audio_file') is-invalid @enderror" name="audio_file" accept="audio/*">
                    <small class="text-muted">Tải file âm thanh chứa nội dung câu hỏi nghe (Tối đa 20MB)</small>
                    @error('audio_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Khối động riêng biệt cho kỹ năng Đọc (Nhập đoạn văn tư liệu) --}}
                <div class="col-md-12 d-none" id="passage_block">
                    <label class="form-label fw-semibold text-primary"><i class="bi bi-file-text"></i> Nội dung đoạn văn Reading tư liệu</label>
                    <textarea class="form-control @error('passage') is-invalid @enderror" name="passage" rows="6" placeholder="Dán nội dung bài đọc Reading vào đây...">{{ old('passage') }}</textarea>
                    @error('passage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Hướng dẫn/Đề bài chung --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Nội dung câu hỏi tự luận / Yêu cầu đề bài chung</label>
                    <textarea class="form-control @error('content') is-invalid @enderror" name="content" rows="4" placeholder="Nhập ghi chú hoặc nội dung mô tả nhiệm vụ...">{{ old('content') }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Đính kèm tệp văn bản khác --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Tài liệu đính kèm hệ thống phụ (PDF/Word/Image)</label>
                    <input type="file" class="form-control" name="attachment">
                </div>

                {{-- File Excel/CSV nhập câu hỏi hàng loạt dành cho Trắc nghiệm --}}
                <div class="col-md-6" id="import_questions_block">
                    <label class="form-label fw-semibold text-secondary">Import danh sách câu hỏi Trắc nghiệm</label>
                    <input type="file" class="form-control" name="import_file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    <small class="text-muted"><a href="{{ route('teacher.assignments.template') }}" class="text-decoration-none"><i class="bi bi-download"></i> Tải file cấu trúc mẫu tại đây</a></small>
                </div>
            </div>

            <div class="text-center mt-5">
                <button type="submit" class="btn text-white fw-bold px-5" style="background-color: var(--primary-color, #990000); height: 46px; border-radius: 4px;">
                    <i class="bi bi-floppy-fill me-1"></i> LƯU VÀO NGÂN HÀNG ĐỀ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const skillSelect = document.getElementById('skill_select');
        const typeSelect = document.getElementById('assignment_type');
        const audioBlock = document.getElementById('audio_file_block');
        const passageBlock = document.getElementById('passage_block');
        const importBlock = document.getElementById('import_questions_block');

        function handleSkillToggle() {
            const currentSkill = skillSelect.value;
            
            if (currentSkill === 'Nghe') {
                audioBlock.classList.remove('d-none');
            } else {
                audioBlock.classList.add('d-none');
            }

            if (currentSkill === 'Đọc') {
                passageBlock.classList.remove('d-none');
            } else {
                passageBlock.classList.add('d-none');
            }
        }

        function handleTypeToggle() {
            if (typeSelect.value === 'Tự luận') {
                importBlock.classList.add('d-none');
            } else {
                importBlock.classList.remove('d-none');
            }
        }

        skillSelect.addEventListener('change', handleSkillToggle);
        typeSelect.addEventListener('change', handleTypeToggle);

        handleSkillToggle();
        handleTypeToggle();
    });
</script>
@endsection