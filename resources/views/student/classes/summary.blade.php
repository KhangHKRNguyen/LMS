@extends('layouts.student_classroom')

@section('title', 'Kết quả tổng kết khóa học')

@section('class_content')
<div class="mb-4">
    <h5 class="fw-bold m-0 text-dark">KẾT QUẢ TỔNG KẾT KHÓA HỌC</h5>
    <small class="text-muted fw-semibold">Lớp học: {{ $class->class_name }} | Khóa học: {{ $class->course->name ?? 'N/A' }}</small>
</div>

@if(!$isApproved)
    {{-- TRƯỜNG HỢP 1: CHƯA ĐƯỢC TA DUYỆT -> HIỂN THỊ THÔNG BÁO CHỜ TRỰC QUAN --}}
    <div class="card border-0 shadow-sm text-center p-5 mt-3" style="background-color: #FFF7ED; border-left: 5px solid #F97316 !important;">
        <div class="card-body">
            <i class="bi bi-hourglass-split display-4 text-warning mb-3 d-block animate-pulse"></i>
            <h5 class="fw-bold text-dark mb-2">Kết quả đang được xử lý</h5>
            <p class="text-secondary mx-auto mb-0" style="max-width: 600px; font-size: 14px; line-height: 1.6;">
                Hiện tại, bảng điểm tổng kết và đánh giá đầu ra của bạn đang được Giảng viên và Trợ giảng rà soát, chấm điểm. Kết quả chính thức sẽ hiển thị ngay sau khi được phê duyệt thành công. Vui lòng quay lại sau!
            </p>
        </div>
    </div>
@else
    {{-- TRƯỜNG HỢP 2: ĐÃ ĐƯỢC TA DUYỆT -> HIỂN THỊ CHI TIẾT TRƯỜNG DỮ LIỆU CỦA RIÊNG HỌC VIÊN --}}
    <div class="row g-4">
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle text-white shadow-sm" 
                         style="width: 70px; height: 70px; background-color: #800000; font-size: 28px;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $student->name }}</h5>
                    <p class="text-muted small font-monospace mb-3">{{ $student->email }}</p>
                    
                    <hr class="my-3 text-muted opacity-25">

                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-muted">Kết quả đầu ra:</span>
                        @if($outputStatus === 'Đạt')
                            <span class="badge bg-success px-3 py-2 fw-bold shadow-sm" style="font-size: 13px;">
                                ĐẠT
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2 fw-bold shadow-sm" style="font-size: 13px;">
                                KHÔNG ĐẠT
                            </span>
                        @endif
                    </div>
                    
                    <small class="text-muted font-monospace d-block mt-3" style="font-size: 11px;">
                        Đã phê duyệt ngày: 
                        {{ $learningResult->approved_date ? \Carbon\Carbon::parse($learningResult->approved_date)->format('d/m/Y') : '--/--/----' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold text-dark m-0">CHI TIẾT CHỈ SỐ ĐÁNH GIÁ</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        
                        <div class="col-sm-6">
                            <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between h-100" style="background-color: #FFF7ED; border-color: #FED7AA !important;">
                                <div>
                                    <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Điểm số tổng kết</small>
                                    <span class="fs-3 fw-black" style="color: #C2410C;">
                                        {{ isset($learningResult->final_grade) ? number_format($learningResult->final_grade, 2) : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between h-100" style="background-color: #F8FAFC; border-color: #E2E8F0 !important;">
                                <div>
                                    <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Yêu cầu khóa học</small>
                                    <span class="fw-bold text-dark d-block mt-1" style="font-size: 14px;">
                                        Điểm >= {{ $class->course->output_overall ?? '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between" style="background-color: #FEF2F2; border-color: #FEE2E2 !important;">
                                <div>
                                    <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Số buổi nghỉ học (Vắng)</small>
                                    <span class="fs-3 fw-bold {{ $totalAbsent >= 3 ? 'text-danger' : 'text-dark' }}">
                                        {{ $totalAbsent }} buổi
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between" style="background-color: #FFFBEB; border-color: #FEF3C7 !important;">
                                <div>
                                    <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Số lần thiếu bài tập</small>
                                    <span class="fs-3 fw-bold {{ $totalMissing >= 1 ? 'text-warning' : 'text-dark' }}">
                                        {{ $totalMissing }} bài
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 p-3 rounded-3 bg-light border-start border-3" style="border-color: #800000 !important;">
                        <h6 class="fw-bold text-dark small mb-1"> Lưu ý quy chế học tập:</h6>
                        <ul class="m-0 text-muted ps-3 font-monospace" style="font-size: 12px; line-height: 1.6;">
                            <li>Học viên vắng quá số buổi quy định hoặc không đạt mức điểm tối thiểu sẽ tính là Không đạt.</li>
                            <li>Nếu có bất kỳ thắc mắc nào về bảng điểm tổng kết, vui lòng liên hệ trực tiếp Trợ giảng lớp học để được hỗ trợ phúc khảo.</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endif
@endsection