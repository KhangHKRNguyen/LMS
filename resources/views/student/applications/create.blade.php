@extends('student.layouts.app')

@section('title', isset($application) ? 'Chỉnh sửa đơn từ' : 'Tạo đơn yêu cầu mới')

@section('content')
<div class="mb-3">
    <a href="{{ route('student.applications.index') }}" class="text-decoration-none text-secondary fw-medium fs-7">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách đơn từ
    </a>
</div>

<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">{{ isset($application) ? 'Chỉnh sửa đơn từ #APP-' . $application->id : 'Tạo đơn yêu cầu mới' }}</h4>
    <p class="text-muted m-0 fs-7">Vui lòng điền đầy đủ và chính xác các thông tin để nhà trường tiến hành phê duyệt nhanh nhất.</p>
</div>

<form action="{{ isset($application) ? route('student.applications.update', $application->id) : route('student.applications.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($application))
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                
                <div class="mb-3.5">
                    <label class="form-label fw-bold text-dark fs-7">Loại đơn từ / Yêu cầu <span class="text-danger">*</span></label>
                    <select class="form-select border-secondary-subtle py-2.5 fs-7" name="type" required>
                        <option value="" selected disabled>-- Chọn loại đơn từ bạn muốn gửi --</option>
                        <option value="bao_luu" {{ (isset($application) && $application->type == 'bao_luu') ? 'selected' : '' }}>Đơn xin bảo lưu kết quả học tập</option>
                        <option value="mien_giam" {{ (isset($application) && $application->type == 'mien_giam') ? 'selected' : '' }}>Đơn xin miễn giảm học phí</option>
                        <option value="chuyen_ca" {{ (isset($application) && $application->type == 'chuyen_ca') ? 'selected' : '' }}>Đơn xin chuyển ca học / Đổi lớp</option>
                        <option value="phuc_khao" {{ (isset($application) && $application->type == 'phuc_khao') ? 'selected' : '' }}>Đơn xin chấm phúc khảo bài thi</option>
                        <option value="khac" {{ (isset($application) && $application->type == 'khac') ? 'selected' : '' }}>Yêu cầu hỗ trợ hành chính khác</option>
                    </select>
                </div>

                <div class="mb-3.5 mt-3">
                    <label class="form-label fw-bold text-dark fs-7">Tiêu đề đơn ngắn gọn <span class="text-danger">*</span></label>
                    <input type="text" class="form-control border-secondary-subtle py-2.5 fs-7" name="title" 
                           placeholder="Ví dụ: Đơn xin chuyển từ ca 2 sang ca 3 môn Laravel nâng cao" 
                           value="{{ $application->title ?? '' }}" required>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label fw-bold text-dark fs-7">Nội dung giải trình & Lý do cụ thể <span class="text-danger">*</span></label>
                    <textarea class="form-control border-secondary-subtle p-3 fs-7" name="reason" rows="6" 
                              placeholder="Trình bày rõ ràng lý do, hoàn cảnh hoặc đề xuất cụ thể của bạn để ban giám hiệu và phòng ban liên quan xét duyệt..." required>{{ $application->reason ?? '' }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('student.applications.index') }}" class="btn btn-light border fw-bold px-3 py-2 fs-7 text-secondary">
                        Hủy bỏ
                    </a>
                    
                    <button type="submit" name="status" value="draft" class="btn btn-outline-secondary fw-bold px-3 py-2 fs-7">
                        <i class="bi bi-bookmark"></i> Lưu bản nháp
                    </button>
                    
                    <button type="submit" name="status" value="pending" class="btn text-white fw-bold px-4 py-2 fs-7" style="background-color: var(--primary-color);">
                        <i class="bi bi-send-fill"></i> Gửi đơn ngay
                    </button>
                </div>

            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-3.5 mb-4" style="border-radius: 8px;">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 fs-7">
                    <i class="bi bi-paperclip text-danger"></i> Tài liệu minh chứng (Nếu có)
                </h6>
                <div class="mb-3">
                    <p class="text-muted fs-8 lh-base">Đính kèm các giấy tờ liên quan (Giấy khám sức khỏe, quyết định công tác, ảnh chụp...) để tăng tỷ lệ duyệt đơn.</p>
                    <input class="form-control border-secondary-subtle fs-7" type="file" name="attachment" id="formFile">
                </div>
                @if(isset($application) && $application->attachment)
                    <div class="p-2 rounded bg-light border d-flex align-items-center justify-content-between">
                        <small class="text-truncate text-dark fw-medium fs-8 style="max-width: 80%;"">
                            <i class="bi bi-file-earmark-check-fill text-success"></i> {{ $application->attachment_name }}
                        </small>
                        <a href="#" class="text-danger fs-8 fw-bold text-decoration-none">Xóa</a>
                    </div>
                @endif
            </div>

            <div class="card border-0 shadow-sm p-3.5" style="border-radius: 8px; background-color: #FFFDE6;">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2.5 fs-7 text-uppercase" style="color: #8B0000 !important;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Quy định xét duyệt
                </h6>
                <ul class="ps-3 text-secondary fs-8 mb-0 lh-lg">
                    <li>Đơn lưu nháp có thể sửa đổi hoặc xóa bất cứ lúc nào.</li>
                    <li>Đơn đã gửi đi ở trạng thái <strong>Chờ duyệt</strong> chỉ có thể thu hồi hoặc xóa trực tiếp từ danh sách.</li>
                    <li>Thời gian xử lý đơn từ hành chính thông thường là từ <strong>24h - 48h</strong> làm việc.</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection