@extends('layouts.admin')

@section('title', 'Tạo lớp học mới')

@section('admin_content')
<div class="mb-4 container-fluid" style="max-width: 700px;">
    <a href="{{ route('admin.classes.index') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách
    </a>
</div>

<div class="card shadow-sm border-0 mx-auto mb-5" style="max-width: 700px; border-radius: 8px;">
    <div class="card-body p-5">
        <h4 class="text-center fw-bold mb-5" style="color: #990000; letter-spacing: 0.5px;">THÊM MỚI LỚP HỌC</h4>

        <form action="{{ route('admin.classes.store') }}" method="POST">
            @csrf
            
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Tên lớp học <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('class_name') is-invalid @enderror" name="class_name" placeholder="Ví dụ: Luyện Writing nâng cao" style="height: 44px;" value="{{ old('class_name') }}">
                    @error('class_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Khóa học chuyên môn <span class="text-danger">*</span></label>
                    <select class="form-select @error('course_id') is-invalid @enderror" name="course_id" style="height: 44px;">
                        <option value="">-- Chọn danh mục khóa học --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }} (Mục tiêu: {{ $course->output_target }})</option>
                        @endforeach
                    </select>
                    @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Lịch học hàng tuần (Tích chọn các ngày học) <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light">
                        @foreach([
                            1 => 'Thứ 2',
                            2 => 'Thứ 3',
                            3 => 'Thứ 4',
                            4 => 'Thứ 5',
                            5 => 'Thứ 6',
                            6 => 'Thứ 7'
                        ] as $value => $label)
                            <div class="form-check">
                                <input class="form-check-input @error('days_of_week') is-invalid @enderror" 
                                    type="checkbox" 
                                    name="days_of_week[]" 
                                    value="{{ $value }}" 
                                    id="day_{{{ $value }}}"
                                    {{ is_array(old('days_of_week')) && in_array($value, old('days_of_week')) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="day_{{{ $value }}}">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('days_of_week') 
                        <div class="text-danger small mt-1">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Phòng học / Địa điểm học</label>
                    <input type="text" class="form-control" name="room" placeholder="Ví dụ: Phòng 402-A2 hoặc Link Zoom..." style="height: 44px;" value="{{ old('room') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Ngày bắt đầu lớp học <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_time') is-invalid @enderror" name="start_time" style="height: 44px;" value="{{ old('start_time') }}">
                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Trạng thái lớp <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" style="height: 44px;">
                        <option value="Đang mở" {{ old('status') == 'Đang mở' ? 'selected' : '' }}>Đang mở</option>
                        <option value="Đã đóng" {{ old('status') == 'Đã đóng' ? 'selected' : '' }}>Đã đóng</option>
                    </select>
                </div>
            </div>

            <div class="text-center mt-5">
                <button type="submit" class="btn text-white fw-bold px-5 shadow-sm" style="background-color: #990000; height: 46px; border-radius: 4px;">XÁC NHẬN LƯU</button>
            </div>
        </form>
    </div>
</div>
@endsection