@extends('student.classes.layout')

@section('title', 'Kết quả học tập môn')
@section('active_grades', 'active-custom')

@section('class_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-dark m-0 text-uppercase"><i class="bi bi-bar-chart-line-fill text-primary"></i> Phiếu điểm và đánh giá năng lực cá nhân</h6>
</div>

<div class="card card-body shadow-sm border-0 mb-4 p-4" style="border-radius: 8px; background-color: #F8FAFC;">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="fw-bold text-dark mb-1">Kết luận đánh giá từ Trợ giảng & Giảng viên:</h6>
            <p class="text-muted m-0 fs-7">Học viên đi học đầy đủ, làm bài tập đầy đủ đúng hạn, tiếp thu bài tốt trên lớp.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="fs-8 fw-bold text-secondary text-uppercase d-block mb-1">Trạng thái môn học</span>
            <span class="badge bg-success text-white fw-bold px-3 py-2 fs-7" style="border-radius: 4px;"><i class="bi bi-patch-check"></i> ĐỦ ĐIỀU KIỆN ĐẠT MÔN</span>
        </div>
    </div>
</div>

<div class="table-responsive shadow-sm" style="border-radius: 8px;">
    <table class="table-arena m-0">
        <thead>
            <tr>
                <th style="width: 80px;">STT</th>
                <th style="text-align: left; padding-left: 20px;">Tên Đầu Điểm Thành Phần</th>
                <th style="width: 160px;">Trọng Số (%)</th>
                <th style="width: 180px;">Điểm Số Đạt Được</th>
                <th style="width: 240px; text-align: left; padding-left: 15px;">Ghi Chú Đánh Giá</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="text-align: left; padding-left: 20px;" class="fw-semibold text-dark">Điểm chuyên cần (Điểm danh)</td>
                <td class="text-secondary fw-medium">10%</td>
                <td class="fw-bold text-dark">15 / 15 buổi <small class="text-success">(100%)</small></td>
                <td style="text-align: left; padding-left: 15px;" class="text-muted fs-7">Đi học đầy đủ đúng giờ.</td>
            </tr>
            <tr>
                <td>2</td>
                <td style="text-align: left; padding-left: 20px;" class="fw-semibold text-dark">Điểm kiểm tra giữa kỳ</td>
                <td class="text-secondary fw-medium">30%</td>
                <td class="fw-bold text-dark fs-6 text-danger" style="color: var(--primary-color) !important;">8.5 / 10</td>
                <td style="text-align: left; padding-left: 15px;" class="text-muted fs-7">Logics xử lý tốt, giao diện tối ưu.</td>
            </tr>
            <tr>
                <td>3</td>
                <td style="text-align: left; padding-left: 20px;" class="fw-semibold text-dark">Điểm thi bảo vệ cuối kỳ</td>
                <td class="text-secondary fw-medium">60%</td>
                <td class="fw-bold text-dark fs-6 text-danger" style="color: var(--primary-color) !important;">9.0 / 10</td>
                <td style="text-align: left; padding-left: 15px;" class="text-muted fs-7">Bảo vệ đồ án xuất sắc, trả lời vấn đáp tốt.</td>
            </tr>
            <tr class="bg-light fw-bold" style="border-top: 2px solid #CBD5E1;">
                <td>#</td>
                <td style="text-align: left; padding-left: 20px;" class="text-uppercase text-dark">ĐIỂM TỔNG KẾT MÔN HỌC</td>
                <td class="text-dark">100%</td>
                <td class="text-success fs-5 fw-extrabold">8.8 / 10</td>
                <td style="text-align: left; padding-left: 15px;" class="text-success fw-bold fs-7"><i class="bi bi-star-fill"></i> Hoàn thành Tốt</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection