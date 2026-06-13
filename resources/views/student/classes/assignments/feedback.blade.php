@extends('student.classes.layout')

@section('title', 'Phản hồi bài làm')
@section('active_assignments', 'active-custom')

@section('class_content')
<!-- THANH ĐIỀU HƯỚNG QUAY LẠI -->
<div class="mb-3">
    <a href="#" class="text-decoration-none text-secondary fw-medium fs-7">
        <i class="bi bi-arrow-left"></i> Quay lại trang chấm bài chi tiết
    </a>
</div>

<!-- THÔNG TIN TỔNG QUAN VỀ LƯỢT CHẤM (Đồng bộ metadata từ image_ec9b4a.png) -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
    <div class="card-body p-3 bg-white" style="border-radius: 8px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="fs-8 text-uppercase fw-bold text-muted d-block">Hộp thoại hỗ trợ học viên</span>
                <h5 class="fw-bold m-0 text-dark" style="color: #8B0000 !important;">
                    Trao đổi về: Đề kiểm tra Speaking - Lượt 1
                </h5>
                <small class="text-muted">Giảng viên phụ trách: <strong>Cô Nguyễn Thị B (Grader)</strong></small>
            </div>
            <div class="text-md-end d-flex align-items-center gap-3">
                <div class="px-3 py-1.5 rounded text-center" style="background-color: #FFFDE6; border: 1px solid #E2E8F0;">
                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Điểm số</span>
                    <strong class="text-dark fs-6">8.5 / 10</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BỐ CỤC KHUNG CHAT SỬ DỤNG TÔNG MÀU FIGMA -->
<div class="row g-3">
    <!-- Cột trái (4 cột): Tóm tắt nhanh nhận xét cũ để tiện đối chiếu khi chat -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px; background-color: #FFFDE6;">
            <div class="card-body p-3.5">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 fs-7 text-uppercase tracking-wider">
                    <i class="bi bi-journal-text text-danger"></i> Đánh giá gốc
                </h6>
                
                <div class="mb-3">
                    <span class="fs-8 text-muted d-block fw-semibold">Nhận xét từ giáo viên:</span>
                    <div class="p-2.5 rounded mt-1 fs-7 text-dark fw-medium" style="background-color: #F9C7B6; border-left: 3px solid #8B0000;">
                        "Em làm rất tốt"
                    </div>
                </div>

                <div class="fs-8 text-secondary lh-base">
                    <i class="bi bi-info-circle-fill"></i> <strong>Lưu ý:</strong> Khung chat này dùng để trao đổi trực tiếp với người chấm bài về các tiêu chí điểm số hoặc đề xuất phúc khảo nếu có sai sót.
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải (8 cột): Khung Chat chính thức -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm d-flex flex-column" style="border-radius: 8px; height: 500px; bg-white">
            
            <!-- Header của khung chat -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light" style="border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <div class="d-flex align-items-center gap-2">
                    <div class="position-relative">
                        <img src="https://i.pravatar.cc/100?img=32" class="rounded-circle border" alt="Teacher Avatar" style="width: 38px; height: 38px; object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                    </div>
                    <div>
                        <strong class="text-dark fs-7 d-block">Cô Nguyễn Thị B</strong>
                        <span class="text-success fs-8 d-block fw-medium" style="font-size: 11px;">Giảng viên đang trực tuyến</span>
                    </div>
                </div>
                <span class="badge bg-secondary-subtle text-secondary border fs-8 px-2 py-1">Mã Ticket: #FB-9921</span>
            </div>

            <!-- VÙNG HIỂN THỊ NỘI DUNG TIN NHẮN (Có thanh cuộn) -->
            <div class="flex-grow-1 p-3 overflow-y-auto" id="chatMessageContainer" style="background-color: #F8FAFC;">
                
                <!-- Tin nhắn từ Giảng viên (Bên trái) -->
                <div class="d-flex align-items-start gap-2 mb-3.5 max-w-75">
                    <img src="https://i.pravatar.cc/100?img=32" class="rounded-circle border mt-1" style="width: 28px; height: 28px;" alt="">
                    <div>
                        <div class="p-3 text-dark fs-7 shadow-sm" style="background-color: #F9C7B6; border-radius: 2px 12px 12px 12px; line-height: 1.5;">
                            Chào Văn A, cô đã xem lại file ghi âm phần Speaking Part 2 của em. Em phát âm rất tự nhiên, tuy nhiên tiêu chí từ vựng (Lexical Resource) chưa được đa dạng lắm nên cô giữ mức điểm 8.5 nhé.
                        </div>
                        <small class="text-muted fs-8 d-block mt-1 ms-1">14:32 - Hôm nay</small>
                    </div>
                </div>

                <!-- Tin nhắn từ Học viên (Bên phải - Đẩy sang phải) -->
                <div class="d-flex align-items-start gap-2 mb-3.5 justify-content-end">
                    <div class="text-end" style="max-width: 75%;">
                        <!-- Đồng bộ màu nền vàng kem đặc trưng của bảng câu hỏi Figma -->
                        <div class="p-3 text-dark text-start fs-7 shadow-sm border border-warning-subtle" style="background-color: #FFFDE6; border-radius: 12px 2px 12px 12px; line-height: 1.5;">
                            Dạ vâng thưa cô, em cảm ơn cô đã phản hồi nhanh ạ. Cô cho em hỏi thêm ở câu số 4 phần Writing, em có thể cải thiện thêm cấu trúc câu phức như thế nào để được band cao hơn trong lần làm bài sau không ạ?
                        </div>
                        <small class="text-muted fs-8 d-block mt-1 me-1">14:40 - Vừa xong</small>
                    </div>
                    <img src="https://i.pravatar.cc/100?img=57" class="rounded-circle border mt-1" style="width: 28px; height: 28px;" alt="">
                </div>

            </div>

            <!-- THANH CÔNG CỤ NHẬP TIN NHẮN GỬI ĐI -->
            <div class="p-3 border-top bg-white" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                <form action="#" method="POST" id="chatInputForm">
                    @csrf
                    <div class="input-group">
                        <!-- Nút đính kèm tệp tin tài liệu hoặc ảnh chụp lỗi -->
                        <button class="btn btn-outline-secondary border-secondary-subtle" type="button" title="Đính kèm tài liệu hỗ trợ bài làm">
                            <i class="bi bi-paperclip fs-5"></i>
                        </button>
                        
                        <!-- Ô nhập liệu chính -->
                        <input type="text" class="form-control border-secondary-subtle px-3 fs-7" placeholder="Nhập nội dung câu hỏi phản hồi tại đây..." style="height: 44px;">
                        
                        <!-- Nút gửi màu Đỏ đô đồng bộ nút "Phản hồi" cũ -->
                        <button class="btn text-white px-4 fw-bold text-uppercase fs-8" type="submit" style="background-color: #8B0000; letter-spacing: 0.5px;">
                            Gửi đi <i class="bi bi-send-fill ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
    /* CSS hỗ trợ căn chỉnh giao diện mượt mà */
    #chatMessageContainer {
        scroll-behavior: smooth;
    }
    /* Tùy biến thanh cuộn khung chat thanh mảnh thanh lịch */
    #chatMessageContainer::-webkit-scrollbar {
        width: 6px;
    }
    #chatMessageContainer::-webkit-scrollbar-track {
        background: #F8FAFC;
    }
    #chatMessageContainer::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 4px;
    }
    #chatMessageContainer::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }
</style>
@endsection