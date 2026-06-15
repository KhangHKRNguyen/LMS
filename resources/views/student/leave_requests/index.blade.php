@extends('layouts.student')

@section('title', 'Học viên - Đơn xin nghỉ học')

@section('student_content')
<style>
    #leaveStatusTabs .nav-link {
        color: #6c757d !important;
        border-bottom: 3px solid transparent !important;
        background: none !important;
        transition: all 0.2s ease;
    }
    #leaveStatusTabs .nav-link.active {
        color: #7A0C0C !important;
        border-bottom: 3px solid #7A0C0C !important;
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Quản lý đơn xin nghỉ học</h3>
        <p class="text-muted m-0 fs-7">Tạo, theo dõi trạng thái và quản lý lịch sử xin nghỉ học của bạn.</p>
    </div>
    <button type="button" class="btn btn-danger d-flex align-items-center gap-2 fw-semibold shadow-sm text-white" style="background-color: #7A0C0C;" data-bs-toggle="modal" data-bs-target="#createLeaveModal">
        Thêm đơn mới
    </button>
</div>

<ul class="nav nav-tabs border-0 gap-4 mb-3" id="leaveStatusTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold border-0 px-0 pb-2" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft" type="button" role="tab">
            Đơn nháp ({{ $draftRequests->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold border-0 px-0 pb-2" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
            Chờ xử lý ({{ $pendingRequests->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold border-0 px-0 pb-2" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
            Đã chấp nhận ({{ $approvedRequests->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold border-0 px-0 pb-2" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
            Đã từ chối ({{ $rejectedRequests->count() }})
        </button>
    </li>
</ul>

<div class="tab-content card border-0 shadow-sm p-3 bg-white" style="border-radius:0 0 8px 8px;">
    
    <div class="tab-pane fade show active" id="draft" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th width="5%">STT</th>
                        <th width="15%">Lớp học</th>
                        <th width="20%">Buổi học</th>
                        <th width="30%">Lý do</th>
                        <th width="10%">Minh chứng</th>
                        <th width="20%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($draftRequests as $index => $req)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-secondary">{{ $req->lessonSession->courseClass->class_name }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($req->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                        <td class="text-start text-wrap" style="max-width: 300px; word-break: break-word;">{{ $req->reason }}</td>
                        <td>
                            @if($req->attachment)
                                <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info">Xem</a>
                            @else
                                <span class="text-muted fs-7">Trống</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-warning btn-edit-trigger" data-id="{{ $req->id }}" data-session="{{ $req->lesson_session_id }}" data-reason="{{ $req->reason }}" data-bs-toggle="modal" data-bs-target="#editLeaveModal" title="Sửa đơn">Sửa</button>
                                
                                <form action="{{ route('student.leave_requests.submit', $req->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Gửi đơn">Gửi đơn</button>
                                </form>

                                <form action="{{ route('student.leave_requests.destroy', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn nháp này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Không có đơn nháp nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="pending" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th width="5%">STT</th>
                        <th>Lớp học</th>
                        <th>Buổi xin nghỉ</th>
                        <th>Ngày gửi đơn</th>
                        <th width="35%">Lý do</th>
                        <th>Minh chứng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $index => $req)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-secondary">{{ $req->lessonSession->courseClass->class_name }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($req->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                        <td>{{ $req->submitted_at ? $req->submitted_at->format('d/m/Y H:i') : $req->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-start text-wrap" style="max-width: 300px; word-break: break-word;">{{ $req->reason }}</td>
                        <td>
                            @if($req->attachment)
                                <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info">Xem</a>
                            @else
                                <span class="text-muted fs-7">Trống</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <form action="{{ route('student.leave_requests.withdraw', $req->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-warning text-white fw-medium py-1">Thu hồi</button>
                                </form>
                                <form action="{{ route('student.leave_requests.destroy', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn muốn xóa hoàn toàn đơn đang chờ duyệt này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger py-1">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Không có đơn nào đang chờ duyệt.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="approved" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th width="5%">STT</th>
                        <th>Lớp học</th>
                        <th>Buổi xin nghỉ</th>
                        <th width="35%">Lý do</th>
                        <th>Minh chứng</th>
                        <th>Người duyệt</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRequests as $index => $req)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $req->lessonSession->courseClass->class_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                        <td class="text-start text-wrap" style="max-width: 300px; word-break: break-word;">{{ $req->reason }}</td>
                        <td>
                            @if($req->attachment)
                                <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info">Xem</a>
                            @else
                                <span class="text-muted fs-7">Trống</span>
                            @endif
                        </td>
                        <td><small class="text-success fw-bold">{{ $req->approver->name ?? 'Hệ thống' }}</small></td>
                        <td><span class="badge bg-success shadow-sm">Đã chấp thuận</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đơn nào được duyệt.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="rejected" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th width="5%">STT</th>
                        <th>Lớp học</th>
                        <th>Buổi xin nghỉ</th>
                        <th width="35%">Lý do nghỉ</th>
                        <th>Minh chứng</th>
                        <th>Người từ chối</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rejectedRequests as $index => $req)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $req->lessonSession->courseClass->class_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                        <td class="text-start text-wrap" style="max-width: 300px; word-break: break-word;">{{ $req->reason }}</td>
                        <td>
                            @if($req->attachment)
                                <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info">Xem</a>
                            @else
                                <span class="text-muted fs-7">Trống</span>
                            @endif
                        </td>
                        <td><small class="text-danger fw-bold">{{ $req->approver->name ?? 'Hệ thống' }}</small></td>
                        <td><span class="badge bg-danger shadow-sm">Từ chối</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Không có dữ liệu đơn bị từ chối.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('student.leave_requests.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header text-white" style="background-color: #7A0C0C;">
                <h5 class="modal-title fw-bold">Tạo đơn xin nghỉ học</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Chọn lớp học & Buổi cần xin nghỉ <span class="text-danger">*</span></label>
                    <select name="lesson_session_id" class="form-select" required>
                        <option value="" disabled selected>-- Chọn lớp học và ngày nghỉ cụ thể --</option>
                        @foreach($lessonSessions as $session)
                            <option value="{{ $session->id }}">Lớp: {{ $session->courseClass->class_name }} — (Ngày: {{ \Carbon\Carbon::parse($session->lesson_date)->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Lý do xin nghỉ học <span class="text-danger">*</span></label>
                    <textarea name="reason" rows="4" class="form-control" placeholder="Ghi rõ lý do vắng mặt (Ví dụ: Trùng lịch thi quân sự, bị ốm nằm viện kèm minh chứng...)" required></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Tệp đính kèm / Minh chứng chứng thực <span class="text-muted">(Nếu có)</span></label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text fs-7">Hệ thống nhận định dạng: PDF, JPG, PNG (Tối đa 2MB).</div>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" name="action" value="save_draft" class="btn btn-outline-secondary fw-semibold">Lưu bản nháp</button>
                <button type="submit" name="action" value="submit_now" class="btn text-white fw-bold" style="background-color: #7A0C0C;">Gửi phê duyệt</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="editLeaveForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">Cập nhật đơn xin nghỉ (Bản nháp)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Lớp học & Buổi học áp dụng <span class="text-danger">*</span></label>
                    <select name="lesson_session_id" id="edit_lesson_session_id" class="form-select" required>
                        @foreach($lessonSessions as $session)
                            <option value="{{ $session->id }}">Lớp: {{ $session->courseClass->class_name }} — (Ngày: {{ \Carbon\Carbon::parse($session->lesson_date)->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Lý do xin nghỉ học <span class="text-danger">*</span></label>
                    <textarea name="reason" id="edit_reason" rows="4" class="form-control" required></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Thay tệp đính kèm mới <span class="text-muted">(Để trống nếu giữ nguyên tệp cũ)</span></label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning fw-bold">Cập nhật thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const editButtons = document.querySelectorAll('.btn-edit-trigger');
        const editForm = document.getElementById('editLeaveForm');
        const editSessionSelect = document.getElementById('edit_lesson_session_id');
        const editReasonTextarea = document.getElementById('edit_reason');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const sessionId = this.getAttribute('data-session');
                const reason = this.getAttribute('data-reason');
                
                // Gán dynamic endpoint hành động cho form sửa
                editForm.action = `/student/leave-requests/${id}/update`;
                
                // Đổ dữ liệu cũ vào các ô input trong Modal
                editSessionSelect.value = sessionId;
                editReasonTextarea.value = reason;
            });
        });
    });
</script>
@endsection