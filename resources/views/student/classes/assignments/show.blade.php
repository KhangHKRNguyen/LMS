@extends('student.classes.layout')

@section('title', 'Chi tiết bài tập & Lịch sử nộp')
@section('active_assignments', 'active-custom')

@section('class_content')
<div class="mb-3">
    <a href="{{ route('student.classes.assignments.index') }}" class="text-decoration-none text-secondary fw-medium fs-7">
        <i class="bi bi-chevron-left"></i> Quay lại danh sách bài tập
    </a>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 border-bottom pb-3 mb-3">
            <div>
                <span class="badge bg-danger-subtle text-danger fw-bold mb-2">ASSIGNMENT</span>
                <h4 class="fw-bold text-dark mb-1">[ASSIGN-1] Xây dựng ứng dụng quản lý nhân sự (Giai đoạn 1)</h4>
                <p class="text-muted m-0 fs-7">Ngày giao: 01/06/2026 | Hình thức: Cá nhân làm bài</p>
            </div>
            <div class="text-md-end">
                <span class="text-muted d-block fs-8 fw-semibold text-uppercase mb-1">Hạn nộp cuối cùng</span>
                <span class="badge bg-danger px-3 py-2 fw-bold fs-7" style="border-radius: 4px;">
                    <i class="bi bi-clock-history"></i> 23:59 - 18/06/2026 (Còn 5 ngày)
                </span>
            </div>
        </div>

        <div class="assignment-description text-secondary fs-7 mb-4">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-earmark-text text-danger"></i> Yêu cầu đề bài:</h6>
            <p>1. Thiết kế cơ sở dữ liệu gồm các bảng: `users`, `departments`, `positions`, `salaries` tuân thủ chuẩn 3NF.</p>
            <p>2. Khởi tạo source code dự án Laravel 11, cấu hình kết nối DB và viết đầy đủ các file Migration, Seeder tạo dữ liệu mẫu.</p>
            <p>3. Xây dựng các Route và Controller xử lý CRUD cơ bản cho danh mục Phòng ban (Departments).</p>
            <p class="fw-medium text-dark"><i class="bi bi-paperclip"></i> Tài liệu đính kèm từ giảng viên: 
                <a href="#" class="text-primary text-decoration-none fw-bold"><i class="bi bi-file-earmark-pdf-fill text-danger"></i> Đề-bài-Assignment-Giai-Đoạn-1.pdf</a>
            </p>
        </div>

        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border border-secondary-subtle">
            <div class="fs-7 text-secondary">
                Bạn được phép nộp lại nhiều lần trước khi hết hạn. Hệ thống sẽ ghi nhận lần nộp mới nhất để chấm điểm.
            </div>
            <button type="button" class="btn text-white fw-bold px-4 py-2 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#popupSubmitWorkModal" style="background-color: var(--primary-color); border-radius: 4px; font-size: 14px;">
                <i class="bi bi-cloud-plus-fill"></i> TIẾN HÀNH NỘP BÀI LÀM
            </button>
        </div>
    </div>
</div>

<div class="mb-3">
    <h6 class="fw-bold text-dark m-0 text-uppercase"><i class="bi bi-clock-history text-secondary"></i> Nhật ký lịch sử các lần nộp bài</h6>
</div>

<div class="table-responsive shadow-sm" style="border-radius: 8px;">
    <table class="table-arena m-0">
        <thead>
            <tr>
                <th style="width: 70px;">Lần nộp</th>
                <th style="width: 180px;">Thời gian nộp bài</th>
                <th style="text-align: left; padding-left: 15px;">Tập tin / Đường dẫn nộp</th>
                <th style="width: 140px;">Người chấm</th>
                <th style="width: 140px;">Trạng thái</th>
                <th style="width: 120px;">Điểm số</th>
                <th style="width: 130px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-bold text-dark">2</td>
                <td class="text-secondary fs-7 fw-medium">15:30 - 13/06/2026</td>
                <td style="text-align: left; padding-left: 15px;">
                    <span class="d-block fw-semibold text-dark mb-1"><i class="bi bi-file-earmark-zip-fill text-warning"></i> NhanSu_V2_Final.zip</span>
                    <small class="text-muted text-xs"><i class="bi bi-link-45deg"></i> Link Github: <a href="#" class="text-decoration-none text-primary">github.com/hoanglong/hr-project</a></small>
                </td>
                <td class="fw-medium text-secondary">TA. Nguyễn Đức B</td>
                <td><span class="badge bg-info-subtle text-info fw-bold border border-info-subtle px-2 py-1 fs-8">Đang chờ chấm</span></td>
                <td class="text-muted fw-semibold">-- / 10</td>
                <td>
                    <a href="#" class="btn btn-sm btn-light border text-secondary fw-semibold fs-8 px-2.5 py-1" style="border-radius: 4px;">
                        Xem chi tiết <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="fw-bold text-dark">1</td>
                <td class="text-secondary fs-7 fw-medium">09:15 - 08/06/2026</td>
                <td style="text-align: left; padding-left: 15px;">
                    <span class="d-block fw-semibold text-secondary mb-1"><i class="bi bi-file-earmark-zip-fill text-muted"></i> NhanSu_GiaiDoan1_V1.zip</span>
                </td>
                <td class="fw-medium text-secondary">ThS. Nguyễn Văn A</td>
                <td><span class="badge bg-success-subtle text-success fw-bold border border-success-subtle px-2 py-1 fs-8">Đã chấm điểm</span></td>
                <td class="fw-bold text-success fs-6">8.0 / 10</td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-primary fw-bold fs-8 px-2.5 py-1" style="border-radius: 4px;">
                        Xem kết quả <i class="bi bi-bar-chart-fill"></i>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<div class="modal fade" id="popupSubmitWorkModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="popupSubmitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px;">
            <div class="modal-header bg-dark text-white py-3" style="border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h6 class="modal-title fw-bold text-uppercase" id="popupSubmitLabel"><i class="bi bi-cloud-arrow-up-fill text-warning"></i> Biểu mẫu nộp bài làm hệ thống</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark fs-7">1. Đính kèm tập tin bài làm (Mã nguồn nén ZIP/RAR) <span class="text-danger">*</span></label>
                        <div class="border border-dashed border-secondary-subtle rounded p-4 text-center bg-light position-relative" style="border-style: dashed !important; border-width: 2px !important;">
                            <i class="bi bi-file-earmark-zip text-secondary fs-1 d-block mb-2"></i>
                            <input type="file" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0" name="source_code_file" required style="cursor: pointer;">
                            <span class="text-dark fw-semibold d-block fs-7">Kéo thả file .zip / .rar vào đây hoặc nhấp chuột để chọn</span>
                            <small class="text-muted fs-8">(Kích thước tối đa chấp nhận: 30MB)</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark fs-7">2. Đường dẫn Git repository / Báo cáo trực tuyến (Nếu có)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-secondary-subtle text-secondary"><i class="bi bi-github"></i></span>
                            <input type="url" class="form-control border-secondary-subtle" name="production_link" placeholder="https://github.com/username/repository-name" style="border-radius: 0 6px 6px 0; height: 42px; font-size: 14px;">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark fs-7">3. Ghi chú hoặc câu hỏi bổ sung gửi Thầy/Cô và Trợ giảng</label>
                        <textarea class="form-control border-secondary-subtle" name="student_message" rows="4" placeholder="Em đã cập nhật lại cấu trúc DB tối ưu hơn và fix lỗi ở phần Route điều hướng trong lần nộp này..." style="border-radius: 6px; font-size: 14px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-end gap-2 p-3" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                    <button type="button" class="btn btn-sm btn-secondary px-3 py-2 fw-medium" data-bs-dismiss="modal" style="border-radius: 4px;">Đóng hộp thoại</button>
                    <button type="submit" class="btn btn-sm text-white px-4 py-2 fw-bold" style="background-color: var(--primary-color); border-radius: 4px;">NỘP BÀI LÊN HỆ THỐNG</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection