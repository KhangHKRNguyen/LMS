@extends('student.classes.layout')

@section('title', 'Chi tiết kết quả lần làm bài')
@section('active_assignments', 'active-custom')

@section('class_content')
<div class="mb-3">
    <a href="#" class="text-decoration-none text-secondary fw-medium fs-7">
        <i class="bi bi-chevron-left"></i> Quay lại nhật ký lịch sử bài tập
    </a>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
    <div class="card-body p-4 bg-white" style="border-left: 5px solid #10B981;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Kết quả đánh giá</span>
                <h5 class="fw-bold text-dark m-0">CHI TIẾT BÀI LÀM: LẦN NỘP THỨ 1</h5>
                <p class="text-muted m-0 fs-7 mt-1">Được chấm điểm vào lúc: 14:20 - 09/06/2026 bởi <strong>ThS. Nguyễn Văn A</strong></p>
            </div>
            <div class="text-md-end p-2 px-4 bg-success-subtle text-success rounded text-center">
                <span class="fs-8 fw-bold d-block text-uppercase">Điểm chấm chính thức</span>
                <strong class="fs-3 fw-extrabold">8.0 / 10</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px;">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-file-earmark-check-fill text-primary"></i> Nội dung bài làm đã nộp</h6>
                
                <div class="mb-3 fs-7">
                    <span class="text-muted">Thời gian hệ thống ghi nhận:</span>
                    <strong class="text-dark d-block">09:15 - 08/06/2026 (Nộp sớm trước hạn 10 ngày)</strong>
                </div>

                <div class="mb-3 fs-7">
                    <span class="text-muted d-block mb-1">Tập tin nguồn đính kèm:</span>
                    <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center border border-secondary-subtle">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-zip-fill text-warning fs-3"></i>
                            <div>
                                <strong class="text-dark d-block">NhanSu_GiaiDoan1_V1.zip</strong>
                                <small class="text-muted fs-8">Dung lượng: 12.4 MB</small>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-secondary bg-white fw-semibold fs-8 px-3 py-1.5" style="border-radius: 4px;">
                            <i class="bi bi-download"></i> Tải file xuống
                        </a>
                    </div>
                </div>

                <div class="fs-7">
                    <span class="text-muted">Ghi chú gửi kèm bài làm:</span>
                    <div class="p-3 bg-light rounded text-secondary mt-1 italic-style" style="font-style: italic; border-left: 3px solid #CBD5E1;">
                        "Thưa thầy, em đã làm đầy đủ các yêu cầu CRUD phòng ban và cấu hình Seeder dữ liệu mẫu cho 5 phòng ban như đề bài quy định. Nhờ thầy xem kỹ giúp em phần liên kết khóa ngoại xem đã chuẩn chưa ạ."
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px;">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-chat-left-heart-fill text-danger"></i> Chi tiết nhận xét phản hồi</h6>

                <div class="mb-4">
                    <span class="text-muted fs-7 d-block mb-2">Bảng phân tích điểm thành phần:</span>
                    <div class="bg-light p-3 rounded" style="font-size: 13px;">
                        <div class="d-flex justify-content-between text-secondary mb-2 border-bottom pb-1">
                            <span>1. Đúng cấu trúc CSDL (Chuẩn 3NF):</span>
                            <strong class="text-dark">3.0 / 3.0đ</strong>
                        </div>
                        <div class="d-flex justify-content-between text-secondary mb-2 border-bottom pb-1">
                            <span>2. Viết Migration & Seeder chuẩn:</span>
                            <strong class="text-dark">2.5 / 3.0đ</strong>
                        </div>
                        <div class="d-flex justify-content-between text-secondary mb-2 border-bottom pb-1">
                            <span>3. Logic CRUD & Route hoạt động:</span>
                            <strong class="text-dark">2.5 / 4.0đ</strong>
                        </div>
                    </div>
                </div>

                <div class="fs-7">
                    <span class="text-muted d-block mb-1">Nhận xét tổng quát từ thầy cô:</span>
                    <div class="p-3 bg-warning-subtle text-dark rounded border border-warning-subtle fw-medium lh-base">
                        <p class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> <strong>Ưu điểm:</strong> Cơ sở dữ liệu thiết kế rất tốt, tường minh, có tư duy tổ chức dữ liệu chặt chẽ.</p>
                        <p class="m-0"><i class="bi bi-exclamation-triangle-fill text-warning"></i> <strong>Hạn chế cần fix:</strong> Phần Controller xử lý cập nhật (Update) phòng ban chưa bắt (validate) trùng tên phòng ban, dẫn đến lỗi nếu người dùng nhập trùng. Cần tối ưu lại trong Giai đoạn 2.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection