@extends('layouts.classroom')

@section('title', 'Giao bài tập cho lớp học')

@section('classroom_content')
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
                    Mã đề: <strong class="text-dark">{{ $exam->exam_code }}</strong> &nbsp;|&nbsp; 
                    Thời gian làm bài: <strong class="text-danger">{{ $exam->duration }} phút</strong> &nbsp;|&nbsp; 
                    Số lượng câu hỏi: <strong class="text-dark">{{ $exam->questions_count ?? $exam->questions->count() }} câu</strong>
                </p>
            </div>
            <span class="badge bg-success text-white fw-bold px-3 py-2" style="border-radius: 20px;">Cấu hình sẵn sàng</span>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- CỘT TRÁI: FORM THIẾT LẬP GIAO BÀI --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 8px;">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                    <i class="bi bi-calendar-check-fill text-danger" style="color: var(--primary-color, #990000);"></i> THIẾT LẬP GIAO BÀI
                </h5>
                
                <form action="{{ route('teacher.assignments.store') }}" method="POST">
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

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Mật khẩu phòng thi (Để trống nếu không dùng)</label>
                            <input type="text" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" placeholder="Nhập mã bảo mật kích hoạt" style="height: 44px; border-radius: 6px; letter-spacing: 1px;">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Số lần làm bài tối đa của mỗi học viên</label>
                            <select class="form-select" name="max_attempts" style="height: 44px; border-radius: 6px;">
                                <option value="1" {{ old('max_attempts') == '1' ? 'selected' : '' }}>Chỉ làm bài 1 lần duy nhất</option>
                                <option value="2" {{ old('max_attempts') == '2' ? 'selected' : '' }}>Tối đa 2 lần</option>
                                <option value="3" {{ old('max_attempts') == '3' ? 'selected' : '' }}>Tối đa 3 lần</option>
                                <option value="0" {{ old('max_attempts') == '0' ? 'selected' : '' }}>Không giới hạn số lần làm bài</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn text-white fw-bold w-100 shadow-sm" style="background-color: var(--primary-color, #990000); height: 46px; border-radius: 4px; letter-spacing: 0.5px;">
                            <i class="bi bi-send-check"></i> XÁC NHẬN GIAO CHO LỚP
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
                    <i class="bi bi-layers-half text-secondary"></i> DANH SÁCH LỚP ĐANG GIAO ĐỀ NÀY
                </h5>
                
                <div class="table-responsive" style="border-radius: 6px;">
                    <table class="table table-bordered align-middle text-center m-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã Lớp</th>
                                <th style="text-align: left; padding-left: 15px;">Tên Lớp Học</th>
                                <th style="width: 150px;">Bắt đầu mở</th>
                                <th style="width: 150px;">Hạn cuối nộp</th>
                                <th style="width: 110px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exam->assignments as $assigned)
                            <tr>
                                <td class="fw-bold text-dark">{{ $assigned->courseClass->class_code ?? $assigned->course_class_id }}</td>
                                <td style="text-align: left; padding-left: 15px;" class="fw-medium">
                                    {{ $assigned->courseClass->class_name }}
                                </td>
                                <td class="fs-7 text-muted">{{ $assigned->start_time ? $assigned->start_time->format('d/m/Y H:i') : '---' }}</td>
                                <td class="fs-7 text-danger fw-medium">{{ $assigned->due_time ? $assigned->due_time->format('d/m/Y H:i') : '---' }}</td>
                                <td>
                                    <form action="{{ route('teacher.assignments.destroy', $assigned->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy giao bài kiểm tra này cho lớp này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" style="font-size: 12px;" title="Hủy giao bài cho lớp này">
                                            <i class="bi bi-trash"></i> Hủy
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-muted fw-medium">Đề thi này chưa được giao cho lớp học nào.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-3 bg-light rounded mt-4 border border-secondary-subtle">
                    <p class="m-0 fs-7 text-secondary fw-medium">
                        <i class="bi bi-info-circle-fill text-primary"></i> <strong>Mẹo giảng dạy:</strong> Giáo viên có thể giao một đề thi từ ngân hàng đề cho nhiều lớp học khác nhau với các mốc khung thời gian làm bài hoàn toàn độc lập.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection