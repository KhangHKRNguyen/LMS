@extends('layouts.teacher')

@section('title', 'Giao bài tập cho lớp học')

@section('teacher_content')
<div class="mb-4">
    <a href="{{ route('teacher.exams.index') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại ngân hàng đề
    </a>
</div>

{{-- Khối hiển thị thông tin Đề thi được chọn lấy từ Ngân hàng đề --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius: 8px;">
    <div class="card-body p-4 bg-white" style="border-left: 5px solid var(--primary-color, #990000); border-radius: 8px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <small class="text-muted fw-bold text-uppercase" style="letter-spacing: 0.5px;">Đang chọn bài tập / đề thi:</small>
                <h4 class="fw-bold text-dark mt-1 mb-0">{{ $exam->title }}</h4>
                <p class="text-muted m-0 mt-1 fs-7">
                    Số lượng câu hỏi: <strong class="text-dark">{{ $exam->questions_count ?? $exam->questions->count() }} câu</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- CỘT TRÁI: FORM THIẾT LẬP GIAO BÀI --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 8px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                    THIẾT LẬP GIAO BÀI
                </h5>
                
                <form action="{{ route('teacher.assignments.assign.store', $exam->id) }}" method="POST">
                    @csrf
                    {{-- Truyền ngầm id của đề thi từ ngân hàng đề lên --}}
                    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Chọn lớp học nhận bài <span class="text-danger">*</span></label>
                            <select class="form-select @error('course_class_id') is-invalid @enderror" name="course_class_id" style="height: 44px; border-radius: 6px;" required>
                                <option value="">-- Chọn lớp học --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('course_class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_code ?? $class->id }} - {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="lesson_session_id" class="form-label fw-bold text-dark">
                                    Chọn buổi học nhận bài <span class="text-danger">*</span>
                                </label>
                                <select name="lesson_session_id" id="lesson_session_id" class="form-select @error('lesson_session_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn một buổi học cụ thể --</option>
                                    @foreach($classes as $class)
                                        <optgroup label="Lớp: {{ $class->class_name }}">
                                            @foreach($class->lessonSessions as $session)
                                                <option value="{{ $session->id }}" {{ old('lesson_session_id') == $session->id ? 'selected' : '' }}>
                                                    Buổi học ngày: {{ \Carbon\Carbon::parse($session->lesson_date)->format('d/m/Y') }} (Mã buổi: #{{ $session->id }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('lesson_session_id')
                                    <div class="invalid-feedback fw-semibold">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="duration_minutes" class="form-label fw-bold text-dark">
                                    Thời gian làm bài (Phút) <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                    name="duration_minutes" 
                                    id="duration_minutes" 
                                    class="form-control @error('duration_minutes') is-invalid @enderror" 
                                    min="1" 
                                    placeholder="Ví dụ: 45, 60, 90..." 
                                    value="{{ old('duration_minutes', $exam->duration ?? 60) }}" 
                                    required>
                                @error('duration_minutes')
                                    <div class="invalid-feedback fw-semibold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Thời gian bắt đầu mở đề <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time') }}" style="height: 44px; border-radius: 6px;" required>
                            <small class="text-muted fs-7">Học viên không thể vào thi trước thời gian này.</small>
                            @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Thời gian kết thúc hạn nộp <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('due_time') is-invalid @enderror" name="due_time" value="{{ old('due_time') }}" style="height: 44px; border-radius: 6px;" required>
                            @error('due_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="max_attempts" class="form-label fw-bold">Giới hạn số lần làm lại bài</label>
                            <input type="number" 
                                name="max_attempts" 
                                id="max_attempts" 
                                class="form-control" 
                                value="1" 
                                min="0" {{-- Sửa từ min="1" thành min="0" --}}
                                required>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i> Nhập <strong>0</strong> nếu muốn cho phép học viên làm bài <strong>không giới hạn số lần</strong>.
                            </small>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn text-white fw-bold w-100 shadow-sm" style="background-color: var(--primary-color, #990000); height: 46px; border-radius: 4px; letter-spacing: 0.5px;">
                            XÁC NHẬN GIAO CHO LỚP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- CỘT PHẢI: DANH SÁCH CÁC LỚP ĐANG ĐƯỢC GIAO ĐỀ NÀY --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 8px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                    DANH SÁCH LỚP ĐANG GIAO ĐỀ NÀY
                </h5>
                
                <div class="table-responsive" style="border-radius: 6px;">
                    <table class="table table-bordered align-middle text-center m-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã Lớp</th>
                                <th style="text-align: left; padding-left: 15px;">Tên Lớp Học</th>
                                <th style="width: 120px;">Buổi học</th>
                                <th style="width: 120px;">Bắt đầu mở</th>
                                <th style="width: 120px;">Hạn cuối nộp</th>
                                <th style="width: 110px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($exam->distributions as $dist)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-dark">
                                    {{ $dist->lessonSession->courseClass->class_name ?? 'Không rõ lớp' }}
                                </td>
                                <td class="fw-medium text-secondary text-start">
                                    @if($dist->lessonSession && $dist->lessonSession->courseClass)
                                        <span class="text-muted">(Buổi ngày: {{ \Carbon\Carbon::parse($dist->lessonSession->lesson_date)->format('d/m/Y') }})</span>
                                    @else
                                        <span class="text-muted">Chưa chỉ định buổi học</span>
                                    @endif
                                </td>
                                <td>{{ $dist->open_time ? $dist->open_time->format('H:i d/m/Y') : '---' }}</td>
                                <td>{{ $dist->close_time ? $dist->close_time->format('H:i d/m/Y') : '---' }}</td>
                                <td>
                                    <form action="{{ route('teacher.assignments.destroy', $dist->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy giao bài kiểm tra này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1">
                                            <i class="bi bi-trash"></i> Hủy giao
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-muted text-center fw-medium">Đề thi này chưa được giao cho lớp học nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection