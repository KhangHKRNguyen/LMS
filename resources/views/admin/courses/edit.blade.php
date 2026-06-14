@extends('layouts.admin')

@section('title', 'Cập nhật khóa học')

@section('admin_content')
<div class="mb-4 container-fluid" style="max-width: 750px;">
    <a href="{{ route('admin.courses.index') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách khóa học
    </a>
</div>

<div class="card shadow-sm border-0 mx-auto mb-5" style="max-width: 750px; border-radius: 8px;">
    <div class="card-body p-5">
        <h4 class="text-center fw-bold mb-5" style="color: #990000; letter-spacing: 0.5px;">CẬP NHẬT THÔNG TIN KHÓA HỌC</h4>

        <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Bắt buộc phải có để Laravel hiểu đây là request cập nhật dữ liệu --}}
            
            <div class="row g-4">
                {{-- Tên khóa học --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Tên khóa học <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" 
                           style="height: 44px;" value="{{ old('name', $course->name) }}">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Thời lượng khóa học --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Thời lượng khóa học</label>
                    <input type="text" class="form-control @error('duration') is-invalid @enderror" name="duration" 
                           style="height: 44px;" value="{{ old('duration', $course->duration) }}">
                    @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Chuẩn đầu ra --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary">Chuẩn đầu ra (Cam kết)</label>
                    <input type="text" class="form-control @error('output_target') is-invalid @enderror" name="output_target" 
                           style="height: 44px;" value="{{ old('output_target', $course->output_target) }}">
                    @error('output_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Mô tả tóm tắt khóa học --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-secondary">Mô tả tóm tắt</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Cụm nút gửi biểu mẫu --}}
            <div class="text-center mt-5">
                <button type="submit" class="btn text-white fw-bold px-5 shadow-sm" style="background-color: #990000; height: 46px; border-radius: 4px;">
                    CẬP NHẬT KHÓA HỌC
                </button>
                <a href="{{ route('admin.courses.index') }}" class="btn btn-light border fw-semibold ms-2 px-4" style="height: 46px; line-height: 32px; border-radius: 4px;">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>
@endsection