@extends('layouts.taclassroom')

@section('title', 'Quản lý đơn xin nghỉ học - TA')

@section('taclassroom_content')
<style>
    #leaveStatusTabs .nav-link {
        color: #6c757d !important; /* Mặc định chữ màu xám (text-secondary) */
        border-bottom: 3px solid transparent !important;
        background: none !important;
        transition: all 0.2s ease;
    }
    #leaveStatusTabs .nav-link.active {
        color: #212529 !important; /* Khi active: chữ màu tối (text-dark) */
        border-bottom: 3px solid #DF8A14 !important; /* Hiện thanh cam */
    }
</style>
<div class="mb-4">
    <a href="{{ route('ta.dashboard') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại bảng điều khiển
    </a>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-radius: 8px;">
    <div class="card-body p-4 bg-white" style="border-top: 4px solid #DF8A14; border-radius: 8px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-warning-subtle text-warning fw-bold mb-2 px-2 py-1 fs-8 text-uppercase">Phân hệ xét duyệt đơn</span>
                <h4 class="fw-bold text-dark mb-1">Danh sách đơn xin nghỉ học</h4>
                <p class="text-muted m-0 fs-7">Người phụ trách: <strong>{{ Auth::user()->name }} (TA)</strong></p>
            </div>
            <div class="text-md-end">
                <span class="text-muted d-block fs-7 fw-semibold">ĐƠN CHỜ DUYỆT</span>
                <span class="fs-4 fw-bold text-warning">{{ $pendingRequests->count() }} Đơn</span>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs border-0 gap-4 mb-4" id="leaveStatusTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold border-0 px-0 pb-2 position-relative"
            id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
            Chờ xử lý ({{ $pendingRequests->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold border-0 px-0 pb-2 position-relative"
            id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
            Đã chấp nhận ({{ $approvedRequests->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold border-0 px-0 pb-2 position-relative"
            id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
            Đã từ chối ({{ $rejectedRequests->count() }})
        </button>
    </li>
</ul>

<div class="tab-content card border-0 shadow-sm p-3 bg-white" style="border-radius:0 0 8px 8px;">
    <div class="tab-pane fade show active" id="pending" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Học viên</th>
                        <th>Lớp học</th>
                        <th>Buổi học / Ngày nghỉ</th>
                        <th>Lý do xin nghỉ</th>
                        <th>Minh chứng</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                    <tr>
                        <td><strong>{{ $req->student->name }}</strong><br><small class="text-muted">{{ $req->student->email }}</small></td>
                        <td><span class="badge bg-secondary">{{ $req->lessonSession->courseClass->class_name }}</span></td>
                        <td>Buổi ngày: {{ \Carbon\Carbon::parse($req->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                        <td class="text-wrap" style="min-width: 250px; max-width: 400px; word-break: break-word; white-space: normal;">
                            {{ $req->reason }}
                        </td>
                        <td>
                            @if($req->attachment)
                                <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark-pdf"></i> Xem tệp</a>
                            @else
                                <span class="text-muted fs-7">Không có</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <form action="{{ route('ta.leave_requests.update', $req->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3">Duyệt</button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger fw-bold px-3 btn-reject-trigger" data-id="{{ $req->id }}" data-student="{{ $req->student->name }}" data-bs-toggle="modal" data-bs-target="#rejectModal">Từ chối</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Không có đơn nào cần xử lý.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="approved" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Học viên</th>
                        <th>Lớp học</th>
                        <th>Buổi học</th>
                        <th>Lý do</th>
                        <th>Người duyệt</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRequests as $req)
                    <tr>
                        <td><strong>{{ $req->student->name }}</strong></td>
                        <td>{{ $req->lessonSession->courseClass->class_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                        <td class="text-wrap" style="min-width: 250px; max-width: 400px; word-break: break-word; white-space: normal;">
                            {{ $req->reason }}
                        </td>
                        <td><small class="text-success fw-bold">{{ $req->approver->name ?? 'Hệ thống' }}</small></td>
                        <td><span class="badge bg-success">Đã chấp thuận</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu đơn chấp thuận.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="rejected" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Học viên</th>
                        <th>Lớp học</th>
                        <th>Buổi học</th>
                        <th>Lý do</th>
                        <th>Người từ chối</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rejectedRequests as $req)
                    <tr>
                        <td><strong>{{ $req->student->name }}</strong></td>
                        <td>{{ $req->lessonSession->courseClass->class_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->lessonSession->lesson_date)->format('d/m/Y') }}</td>
                        <td class="text-wrap" style="min-width: 250px; max-width: 400px; word-break: break-word; white-space: normal;">
                            {{ $req->reason }}
                        </td>
                        <td><small class="text-danger fw-bold">{{ $req->approver->name ?? 'Hệ thống' }}</small></td>
                        <td><span class="badge bg-danger">Từ chối</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu đơn bị từ chối.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="rejectForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title fw-bold">Xác nhận từ chối đơn xin nghỉ</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-center fs-6">Bạn chắc chắn muốn từ chối đơn xin nghỉ học của học viên <strong id="studentNameTarget" class="text-danger"></strong> không?</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-sm btn-danger px-4 fw-bold">Từ chối đơn</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // Đổ thông tin học viên động lên Modal khi bấm từ chối
        const rejectButtons = document.querySelectorAll('.btn-reject-trigger');
        const rejectForm = document.getElementById('rejectForm');
        const studentNameTarget = document.getElementById('studentNameTarget');

        rejectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const requestId = this.getAttribute('data-id');
                const studentName = this.getAttribute('data-student');
                
                rejectForm.action = `/ta/leave-requests/${requestId}`;
                studentNameTarget.textContent = studentName;
            });
        });
    });
</script>
@endsection