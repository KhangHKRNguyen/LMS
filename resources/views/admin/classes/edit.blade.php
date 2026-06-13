@extends('layouts.admin')

@section('title', 'Cập nhật lớp học')

@section('admin_content')
<div class="mb-4 container-fluid" style="max-width: 700px;">
    <a href="{{ route('admin.classes.index') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Hủy bỏ và Quay lại
    </a>
</div>

<div class="card shadow-sm border-0 mx-auto mb-5" style="max-width: 700px; border-radius: 8px;">
    <div class="card-body p-5">
        <h4 class="text-center fw-bold mb-5" style="color: #990000; letter-spacing: 0.5px;">CẬP NHẬT THÔNG TIN LỚP HỌC</h4>

        <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Tên lớp học <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('class_name') is-invalid @enderror" name="class_name" value="{{ old('class_name', $class->class_name) }}" style="height: 44px;">
                    @error('class_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Khóa học chuyên môn <span class="text-danger">*</span></label>
                    <select class="form-select" name="course_id" style="height: 44px;">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id', $class->course_id) == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Phòng học / Địa điểm học</label>
                    <input type="text" class="form-control" name="room" value="{{ old('room', $class->room) }}" style="height: 44px;">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time', $class->start_time?->format('Y-m-d')) }}" style="height: 44px;">
                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Ngày kết thúc <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time', $class->end_time?->format('Y-m-d')) }}" style="height: 44px;">
                    @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Trạng thái lớp <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" style="height: 44px;">
                        <option value="Đang mở" {{ old('status', $class->status) == 'Đang mở' ? 'selected' : '' }}>Đang mở</option>
                        <option value="Đã đóng" {{ old('status', $class->status) == 'Đã đóng' ? 'selected' : '' }}>Đã đóng</option>
                    </select>
                </div>
            </div>

            <div class="text-center mt-5">
                <button type="submit" class="btn text-white fw-bold px-5 shadow-sm" style="background-color: #990000; height: 46px; border-radius: 4px;">CẬP NHẬT NGAY</button>
            </div>
        </form>
    </div>
</div>
@endsection