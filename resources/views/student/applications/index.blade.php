@extends('student.layouts.app')

@section('title', 'Quản lý đơn từ học viên')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Quản lý đơn từ và Yêu cầu</h4>
        <p class="text-muted m-0 fs-7">Nơi theo dõi, tạo mới và nhận kết quả phê duyệt các đơn từ hành chính của học viên.</p>
    </div>
    <a href="#" class="btn text-white fw-bold px-3 py-2 d-flex align-items-center gap-2" style="background-color: var(--primary-color); border-radius: 4px; font-size: 14px;">
        <i class="bi bi-plus-circle-fill"></i> TẠO ĐƠN YÊU CẦU MỚI
    </a>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
    <div class="card-body p-2 bg-white" style="border-radius: 8px;">
        <ul class="nav nav-pills custom-application-tabs" id="applicationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active position-relative fw-bold py-2.5 px-4 fs-7" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-status" type="button" role="tab">
                    <i class="bi bi-hourglass-split text-warning me-1"></i> Chờ duyệt
                    <span class="badge bg-warning text-dark ms-1 rounded-pill">1</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative fw-bold py-2.5 px-4 fs-7" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-status" type="button" role="tab">
                    <i class="bi bi-pencil-square text-secondary me-1"></i> Nháp
                    <span class="badge bg-secondary text-white ms-1 rounded-pill">2</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative fw-bold py-2.5 px-4 fs-7" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-status" type="button" role="tab">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Đã duyệt
                    <span class="badge bg-success text-white ms-1 rounded-pill">12</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative fw-bold py-2.5 px-4 fs-7" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-status" type="button" role="tab">
                    <i class="bi bi-x-circle-fill text-danger me-1"></i> Đã từ chối
                    <span class="badge bg-danger text-white ms-1 rounded-pill">1</span>
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content" id="applicationTabsContent">
    
    <div class="tab-pane fade show active" id="pending-status" role="tabpanel" aria-labelledby="pending-tab">
        <div class="table-responsive shadow-sm rounded-3">
            <table class="table table-arena align-middle m-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">STT</th>
                        <th style="width: 140px;">Mã đơn</th>
                        <th style="text-align: left; padding-left: 15px;">Loại đơn từ yêu cầu</th>
                        <th style="width: 160px;">Ngày gửi đơn</th>
                        <th style="width: 180px;">Người tiếp nhận</th>
                        <th style="width: 180px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="fw-bold text-dark">#APP-8821</td>
                        <td style="text-align: left; padding-left: 15px;">
                            <strong class="text-dark d-block">Đơn xin bảo lưu kết quả học tập</strong>
                            <small class="text-muted fs-8">Lý do: Giải quyết công việc gia đình cá nhân</small>
                        </td>
                        <td class="text-secondary fw-medium fs-7">10:45 - 12/06/2026</td>
                        <td class="fw-medium text-dark"><i class="bi bi-person-badge"></i> Phòng Khảo thí</td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-outline-warning fw-bold fs-8 px-2.5 py-1">
                                    <i class="bi bi-arrow-counterclockwise"></i> Thu hồi
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger fw-bold fs-8 px-2.5 py-1"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteApplicationModal" 
                                        data-url="#"> 
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="draft-status" role="tabpanel" aria-labelledby="draft-tab">
        <div class="table-responsive shadow-sm rounded-3">
            <table class="table table-arena align-middle m-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">STT</th>
                        <th style="width: 140px;">Mã đơn</th>
                        <th style="text-align: left; padding-left: 15px;">Nội dung đơn nháp</th>
                        <th style="width: 160px;">Ngày lưu nháp</th>
                        <th style="width: 240px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="text-muted">#APP-8701</td>
                        <td style="text-align: left; padding-left: 15px;">
                            <strong class="text-secondary d-block">Đơn xin miễn giảm học phí học kỳ phụ</strong>
                            <small class="text-muted fs-8">Trạng thái: Chưa đính kèm minh chứng giấy tờ</small>
                        </td>
                        <td class="text-muted fs-7">08:22 - 05/06/2026</td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-outline-primary fw-bold fs-8 px-2.5 py-1">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold fs-8 px-2.5 py-1">
                                    <i class="bi bi-send-fill"></i> Gửi đơn
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger fw-bold fs-8 px-2.5 py-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteApplicationModal" 
                                        data-url="#">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="approved-status" role="tabpanel" aria-labelledby="approved-tab">
        <div class="table-responsive shadow-sm rounded-3">
            <table class="table table-arena align-middle m-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">STT</th>
                        <th style="width: 140px;">Mã đơn</th>
                        <th style="text-align: left; padding-left: 15px;">Loại đơn</th>
                        <th style="width: 160px;">Ngày phê duyệt</th>
                        <th style="width: 180px;">Người duyệt</th>
                        <th style="width: 160px; text-align: center;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="fw-bold text-dark">#APP-7540</td>
                        <td style="text-align: left; padding-left: 15px;">
                            <strong class="text-dark d-block">Đơn xin chuyển ca học môn Laravel nâng cao</strong>
                            <small class="text-muted fs-8">Chuyển từ ca 2 sang ca 3 phòng Lab 402</small>
                        </td>
                        <td class="text-secondary fs-7">16:00 - 28/05/2026</td>
                        <td class="fw-medium text-dark">ThS. Nguyễn Văn A</td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success fw-bold border border-success-subtle px-2.5 py-1 fs-8">
                                <i class="bi bi-patch-check-fill"></i> Đã chấp thuận
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="rejected-status" role="tabpanel" aria-labelledby="rejected-tab">
        <div class="table-responsive shadow-sm rounded-3">
            <table class="table table-arena align-middle m-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">STT</th>
                        <th style="width: 140px;">Mã đơn</th>
                        <th style="text-align: left; padding-left: 15px;">Loại đơn bị từ chối</th>
                        <th style="width: 160px;">Ngày từ chối</th>
                        <th style="width: 180px;">Người xử lý</th>
                        <th style="text-align: left; padding-left: 15px;">Lý do phản hồi từ hệ thống</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="fw-bold text-secondary">#APP-6210</td>
                        <td style="text-align: left; padding-left: 15px;">
                            <strong class="text-dark d-block">Đơn xin khảo thí lại kỹ năng Writing</strong>
                            <small class="text-muted fs-8">Yêu cầu chấm phúc khảo đợt thi tháng 5</small>
                        </td>
                        <td class="text-secondary fs-7">09:15 - 01/06/2026</td>
                        <td class="fw-medium text-dark">Phòng Khảo thí</td>
                        <td style="text-align: left; padding-left: 15px;">
                            <span class="badge bg-danger-subtle text-danger fw-bold border border-danger-subtle px-2 py-0.5 fs-8 mb-1">Đã từ chối</span>
                            <small class="text-danger d-block fw-medium">Lý do: Đã quá thời hạn 7 ngày nộp đơn phúc khảo theo quy định của trung tâm.</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .custom-application-tabs .nav-link {
        color: #64748B;
        border-radius: 6px;
        transition: all 0.15s ease-in-out;
    }
    .custom-application-tabs .nav-link:hover {
        background-color: #F1F5F9;
        color: #1E293B;
    }
    .custom-application-tabs .nav-link.active {
        background-color: var(--primary-color) !important;
        color: white !important;
    }
    .custom-application-tabs .nav-link.active .text-warning,
    .custom-application-tabs .nav-link.active .text-success,
    .custom-application-tabs .nav-link.active .text-danger,
    .custom-application-tabs .nav-link.active .text-secondary {
        color: white !important;
    }
    .fs-9 {
        font-size: 11px !important;
    }
</style>

<div class="modal fade" id="deleteApplicationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle-fill" style="font-size: 54px;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="deleteApplicationModalLabel">Xác nhận xóa đơn?</h5>
                <p class="text-muted fs-7 m-0 lh-base">
                    Hành động này sẽ xóa hoàn toàn dữ liệu của đơn từ khỏi hệ thống của bạn và <strong class="text-danger">không thể phục hồi lại</strong>. Bạn có chắc chắn muốn tiếp tục?
                </p>
            </div>
            <div class="modal-footer border-0 p-3 pt-0 d-flex gap-2">
                <button type="button" class="btn btn-light border flex-grow-1 fw-bold fs-7 py-2.5 text-secondary" style="border-radius: 6px;" data-bs-dismiss="modal">
                    Hủy bỏ
                </button>
                <form action="#" method="POST" id="deleteApplicationForm" class="flex-grow-1 m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100 fw-bold fs-7 py-2.5" style="border-radius: 6px; background-color: #DC3545;">
                        Xóa vĩnh viễn
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteModal = document.getElementById('deleteApplicationModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const deleteUrl = button.getAttribute('data-url');
                const form = deleteModal.querySelector('#deleteApplicationForm');
                form.setAttribute('action', deleteUrl);
            });
        }
    });
</script>
@endsection