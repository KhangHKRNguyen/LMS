@extends('layouts.classroom')

@section('title', 'Workspace - Chấm bài chi tiết')

@section('classroom_content')
<div class="container-fluid py-3" style="background-color: #FAFAFA;">
    {{-- HEADER KHU VỰC THÔNG TIN --}}
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <h6 class="fw-bold m-0"><a href="#" class="text-decoration-none text-dark"><i class="bi bi-arrow-left me-2"></i>LỚP HỌC - U206_PV</a></h6>
        <h5 class="fw-bold text-danger m-0" style="letter-spacing: 1px;">CHẤM BÀI CHI TIẾT</h5>
        <div class="d-flex gap-3 small fw-bold">
            <i class="bi bi-bell-fill text-warning"></i>
            <div class="rounded-circle bg-secondary" style="width:30px; height:30px;"></div>
        </div>
    </div>

    {{-- METADATA BAR --}}
    <div class="d-flex justify-content-center gap-5 fw-bold text-dark mb-4">
        <div>Học viên: <span class="text-secondary ms-2">Nguyễn Văn A</span></div>
        <div>Bài tập: <span class="text-secondary ms-2">Đề luyện thi IELTS</span></div>
        <div>Lượt nộp: <span class="text-secondary ms-2">Lần 1</span></div>
    </div>

    {{-- SPLIT-PANE WORKSPACE --}}
    <form action="#" method="POST" id="gradingForm">
        @csrf
        <div class="row g-4">
            {{-- KHỐI TRÁI: BÀI LÀM HỌC VIÊN --}}
            <div class="col-md-6">
                <div class="p-4 rounded shadow-sm border h-100" style="background-color: #FFFDF0;">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-journal-text me-2"></i>BÀI LÀM HỌC VIÊN</h6>
                    
                    <div class="mb-4 small">
                        <div class="fw-bold mb-1">■ Câu 1 (Trắc nghiệm):</div>
                        <p class="text-muted m-0 ps-3">What is the main idea of the passage?</p>
                        <div class="ps-3 mt-2">
                            <div class="text-success fw-bold mb-1">● A. Education <span class="badge bg-success text-white ms-3 px-2">Đúng</span></div>
                            <div class="text-muted mb-1">○ B. Science</div>
                            <div class="text-muted mb-1">○ C. Technology</div>
                            <div class="text-muted">○ D. Art</div>
                        </div>
                    </div>

                    <div class="mb-4 small">
                        <div class="fw-bold mb-1">■ Câu 4 (Tự luận - Writing):</div>
                        <p class="text-muted m-0 ps-3">[Writing Task 2] Write an essay (at least 250 words) about the impacts of climate change on agriculture.</p>
                        <div class="p-3 rounded border mt-2" style="background-color: #FFD2C4; min-height: 100px; color: #5C2D21;">
                            Bài làm của học viên ......
                        </div>
                    </div>

                    <div class="mb-3 small">
                        <div class="fw-bold mb-1">■ Câu 5 (Nói - Speaking):</div>
                        <p class="text-muted m-0 ps-3">[Speaking Part 2] Describe a book you have read recently. You should say what the book is and explain why you like it.</p>
                        <div class="p-2 border rounded mt-2 d-flex align-items-center gap-3 bg-white shadow-sm">
                            <button type="button" class="btn btn-sm btn-dark"><i class="bi bi-play-fill"></i> Play</button>
                            <div class="w-100 bg-secondary-subtle rounded-pill" style="height: 6px; position:relative;">
                                <div class="bg-danger rounded-pill" style="width: 65%; height:100%;"></div>
                            </div>
                            <span class="text-muted fw-bold font-monospace" style="font-size: 11px;">01:25/02:00</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KHỐI PHẢI: FORM ĐIỀU PHỐI ĐIỂM --}}
            <div class="col-md-6">
                <div class="p-4 rounded shadow-sm border h-100" style="background-color: #FFFDF0;">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-calculator me-2"></i>TỔNG HỢP ĐIỂM SỐ</h6>
                    <div class="small fw-bold text-dark mb-3 ps-2">
                        <div class="mb-2">Điểm tự động chấm: <span class="ms-3 text-secondary">4.0 / 4.0 điểm</span></div>
                        <div>Điểm giáo viên chấm: <span id="teacherTotal" class="ms-3 text-danger">0.0</span> <span class="text-secondary"> / 6.0 điểm</span></div>
                    </div>

                    <h6 class="fw-bold text-dark small mt-4 mb-2"><i class="bi bi-pencil-square me-2"></i>CHẤM ĐIỂM CHI TIẾT TỪNG CÂU</h6>
                    <div class="mb-3 ps-2 small">
                        <label class="fw-bold text-muted mb-1">■ Câu 4 (Tự luận - Writing)</label>
                        <div class="d-flex align-items-center gap-2">
                            <span>Điểm tối đa: 3.0 điểm | Nhập điểm câu này:</span>
                            <input type="number" step="0.1" min="0" max="3" class="form-control form-control-sm text-center input-score" style="width: 70px; background-color: #FFFDF0; border: 1px solid #C1A79E;" required>
                        </div>
                    </div>

                    <div class="mb-4 ps-2 small">
                        <label class="fw-bold text-muted mb-1">■ Câu 5 (Nói - Speaking)</label>
                        <div class="d-flex align-items-center gap-2">
                            <span>Điểm tối đa: 3.0 điểm | Nhập điểm câu này:</span>
                            <input type="number" step="0.1" min="0" max="3" class="form-control form-control-sm text-center input-score" style="width: 70px; background-color: #FFFDF0; border: 1px solid #C1A79E;" required>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-chat-left-text me-2"></i>NHẬN XẾT CỦA GIÁO VIÊN</h6>
                    <textarea class="form-control p-3 small mb-4" rows="4" style="background-color: #FFD2C4; border: 1px solid #C1A79E; color: #5C2D21;" placeholder="Nhập nhận xét chi tiết cấu trúc tốt, từ vựng phong phú..."></textarea>
                </div>
            </div>
        </div>

        {{-- FOOTER ACTION BAR --}}
        <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="button" onclick="triggerErrorFlow()" class="btn btn-sm fw-bold px-3 text-dark border shadow-sm" style="background-color: #FFC4B0;">
                ⚠ Báo lỗi bài làm (File hỏng)
            </button>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm fw-bold px-4 border text-dark" style="background-color: #FFC4B0;">Hủy bỏ</button>
                <button type="submit" class="btn btn-sm fw-bold px-4 text-dark shadow-sm" style="background-color: #CCFFCC; border:1px solid #A3E6A3;">Hoàn tất chấm bài</button>
            </div>
        </div>
    </form>
</div>

{{-- POPUP XÁC NHẬN --}}
<div class="modal fade" id="confirmGradeModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center border-2 border-danger" style="background-color: #FFC4B0;">
            <div class="modal-body p-4">
                <h5 class="fw-bold text-danger mb-4">Xác nhận chấm bài</h5>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn text-white fw-bold px-4 rounded-pill" style="background-color: #800000;" onclick="submitGradingForm()">Đồng ý</button>
                    <button type="button" class="btn bg-white fw-bold px-4 rounded-pill text-dark" data-bs-dismiss="modal">Hủy</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.input-score').forEach(input => {
        input.addEventListener('input', () => {
            let total = 0;
            document.querySelectorAll('.input-score').forEach(inp => {
                total += parseFloat(inp.value) || 0;
            });
            document.getElementById('teacherTotal').innerText = total.toFixed(1);
        });
    });

    function triggerErrorFlow() {
        let reason = prompt("Nhập lý do cụ thể gửi học viên yêu cầu nộp lại bài:");
        if(reason) { alert("Hệ thống đã thu hồi bài lỗi và thông báo cho học viên."); }
    }

    document.getElementById('gradingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var myModal = new bootstrap.Modal(document.getElementById('confirmGradeModal'));
        myModal.show();
    });

    function submitGradingForm() {
        alert("Hệ thống đã lưu điểm và gửi kết quả!");
        window.location.reload();
    }
</script>
@endsection