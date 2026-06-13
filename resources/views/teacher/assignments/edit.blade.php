@extends('layouts.classroom')

@section('title', 'Cập nhật nội dung đề thi')

@section('classroom_content')
<div class="mb-4">
    <a href="{{ route('teacher.assignments.index') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-x-circle"></i> Hủy bỏ và Quay lại Ngân hàng đề
    </a>
</div>

<div class="card shadow-sm border-0 mx-auto" style="max-width: 850px; border-radius: 8px;">
    <div class="card-body p-5">
        <h4 class="text-center fw-bold mb-5" style="color: var(--primary-color, #990000); letter-spacing: 0.5px;">CẬP NHẬT CẤU HÌNH ĐỀ THI GỐC</h4>

        <form action="{{ route('teacher.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row g-4">
                {{-- Tiêu đề đề thi --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Tiêu đề đề thi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $assignment->title) }}" style="height: 44px; border-radius: 6px;" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Hình thức & Kỹ năng --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Hình thức làm bài <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" name="type" style="height: 44px;" required>
                        <option value="Trắc nghiệm" {{ old('type', $assignment->type) == 'Trắc nghiệm' ? 'selected' : '' }}>Trắc nghiệm</option>
                        <option value="Tự luận" {{ old('type', $assignment->type) == 'Tự luận' ? 'selected' : '' }}>Tự luận</option>
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Kỹ năng đánh giá <span class="text-danger">*</span></label>
                    <select class="form-select @error('skill') is-invalid @enderror" name="skill" id="skill_select_edit" style="height: 44px;" required>
                        <option value="Nghe" {{ old('skill', $assignment->skill) == 'Nghe' ? 'selected' : '' }}>Listening (Nghe)</option>
                        <option value="Đọc" {{ old('skill', $assignment->skill) == 'Đọc' ? 'selected' : '' }}>Reading (Đọc)</option>
                        <option value="Viết" {{ old('skill', $assignment->skill) == 'Viết' ? 'selected' : '' }}>Writing (Viết)</option>
                        <option value="Nói" {{ old('skill', $assignment->skill) == 'Nói' ? 'selected' : '' }}>Speaking (Nói)</option>
                    </select>
                    @error('skill') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Thời lượng thi --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary">Thời lượng thi (Phút) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" name="duration_minutes" value="{{ old('duration_minutes', $assignment->duration_minutes) }}" style="height: 44px;" required>
                    @error('duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Khối động kỹ năng Nghe --}}
                <div class="col-md-12 d-none" id="audio_file_block_edit">
                    <label class="form-label fw-semibold text-success"><i class="bi bi-music-note-beamed"></i> Thay đổi file âm thanh mới (.mp3, .wav)</label>
                    <input type="file" class="form-control" name="audio_file" accept="audio/*">
                    @if($assignment->audio_path)
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-file-earmark-music"></i> File hiện tại: <code>{{ basename($assignment->audio_path) }}</code>
                        </div>
                    @endif
                </div>

                {{-- Khối động kỹ năng Đọc --}}
                <div class="col-md-12 d-none" id="passage_block_edit">
                    <label class="form-label fw-semibold text-primary"><i class="bi bi-file-text"></i> Nội dung đoạn văn Reading tư liệu</label>
                    <textarea class="form-control" name="passage" rows="6">{{ old('passage', $assignment->passage) }}</textarea>
                </div>

                {{-- Yêu cầu đề bài chung --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Nội dung câu hỏi tự luận / Yêu cầu đề bài</label>
                    <textarea class="form-control" name="content" rows="4">{{ old('content', $assignment->content) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Cập nhật tài liệu đính kèm phụ (Nếu muốn đổi file cũ)</label>
                    <input type="file" class="form-control" name="attachment">
                </div>
            </div>

            <div class="text-center mt-5">
                <button type="submit" class="btn text-white fw-bold px-5" style="background-color: var(--primary-color, #990000); height: 46px;">
                    <i class="bi bi-check-circle me-1"></i> LƯU CẬP NHẬT
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const skillSelect = document.getElementById('skill_select_edit');
        const audioBlock = document.getElementById('audio_file_block_edit');
        const passageBlock = document.getElementById('passage_block_edit');

        function handleSkillToggleEdit() {
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

        skillSelect.addEventListener('change', handleSkillToggleEdit);
        handleSkillToggleEdit();
    });
</script>
@endsection