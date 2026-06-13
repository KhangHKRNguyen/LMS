@extends('layouts.app')

@section('title', 'Quản lý đơn xin nghỉ học - TA')

@section('content')
<div class="mb-4">
    <a href="{{ route('ta.dashboard') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại bảng điều khiển
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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
                <span class="fs-4 fw-bold text-warning">{{ $pendingRequests->count() }} Đơn mới</span>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs border-bottom mb-4" id="leaveStatusTabs" role="tablist" style="gap: 5px;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold px-4 py-2 position-relative text-dark" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-pane" type="button" role="tab" style="border-radius: 6px 6px 0 0; border: none; border-bottom: 3px solid #DF8A14 !important;">
            <i class="bi bi-hourglass-split text-warning"></i> Chờ duyệt
            @if($pendingRequests->count() > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">{{ $pendingRequests->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold px-4 py-2 text-secondary" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-pane" type="button" role="tab" style="border-radius: 6px 6px 0 0; border: none;">
            <i class="bi bi-check-circle-fill text-success"></i> Đã duyệt ({{ $approvedRequests->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold px-4 py-2 text-secondary" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-pane" type="button" role="tab" style="border-radius: 6px 6px 0 0; border: none;">
            <i class="bi bi-x-circle-fill text-danger"></i> Đã từ chối ({{ $rejectedRequests->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="leaveStatusTabsContent">
    
    {{-- TAB: CHỜ DUYỆT --}}
    <div class="tab-pane fade show active" id="pending-pane" role="tabpanel" aria-labelledby="pending-tab">
        <div class="table-responsive shadow-sm" style="border-radius: 8px;">
            <table class="table table-hover align-middle m-0 bg-white">
                <thead class="table-light text-secondary">
                    <tr>
                        <th style="width: 60px;" class="text-center">STT</th>
                        <th style="width: 160px;">Lớp học</th>
                        <th style="width: 180px;">Học Viên Gửi Đơn</th>
                        <th style="width: 140px;" class="text-center">Ngày Xin Nghỉ</th>
                        <th style="text-align: left; padding-left: 15px;">Lý Do Nghỉ Học Chi Tiết</th>
                        <th style="width: 180px;" class="text-center">Hành Động Xử Lý</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $index => $req)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td>
                            <span class="fw-bold text-dark d-block fs-7">{{ $req->lessonSession->courseClass->class_name ?? 'N/A' }}</span>
                        </td>
                        <td class="fw-medium">
                            <span class="text-dark d-block">{{ $req->student->name ?? 'N/A' }}</span>
                            <small class="text-muted fs-8">Mã HV: {{ $req->student->code ?? 'HV-'.$req->user_id }}</small>
                        </td>
                        <td class="text-center text-secondary fw-medium">{{ \Carbon\Carbon::parse($req->request_date)->format('d/m/Y') }}</td>
                        <td style="text-align: left; padding-left: 15px;" class="text-muted fs-7">{{ $req->reason }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <form action="{{ route('ta.leave_requests.update', $req->id) }}" method="POST" onsubmit="return confirm('Xác nhận duyệt chấp nhận đơn nghỉ học này?')">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-success text-white py-1 px-2 fw-medium" style="font-size: 12px; border-radius: 4px;">
                                        <i class="bi bi-check-lg"></i> Duyệt
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 fw-medium btn-reject-trigger" 
                                        data-id="{{ $req->id }}" 
                                        data-student="{{ $req->student->name ?? 'Học viên' }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmRejectModal" 
                                        style="font-size: 12px; border-radius: 4px;">
                                    <i class="bi bi-x-lg"></i> Từ chối
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center p-4 text-muted">Không có đơn xin nghỉ nào đang chờ duyệt.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB: ĐÃ DUYỆT --}}
    <div class="tab-pane fade" id="approved-pane" role="tabpanel" aria-labelledby="approved-tab">
        <div class="table-responsive shadow-sm" style="border-radius: 8px;">
            <table class="table table-hover align-middle m-0 bg-white">
                <thead class="table-light text-secondary">
                    <tr>
                        <th style="width: 60px;" class="text-center">STT</th>
                        <th>Lớp học</th>
                        <th style="width: 180px;">Học Viên</th>
                        <th style="width: 140px;" class="text-center">Ngày Nghỉ</th>
                        <th style="text-align: left; padding-left: 15px;">Lý Do</th>
                        <th style="width: 150px;" class="text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRequests as $index => $req)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td><span class="fw-bold text-dark fs-7">{{ $req->lessonSession->courseClass->class_name ?? 'N/A' }}</span></td>
                        <td class="fw-medium">
                            <span class="text-dark d-block">{{ $req->student->name ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center text-secondary fw-medium">{{ \Carbon\Carbon::parse($req->request_date)->format('d/m/Y') }}</td>
                        <td style="text-align: left; padding-left: 15px;" class="text-muted fs-7">{{ $req->reason }}</td>
                        <td class="text-center"><span class="badge bg-success-subtle text-success fw-bold border border-success-subtle px-2 py-1 fs-8" style="border-radius: 4px;"><i class="bi bi-check2"></i> Đã chấp nhận</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center p-4 text-muted">Chưa có đơn nào được duyệt.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB: ĐÃ TỪ CHỐI --}}
    <div class="tab-pane fade" id="rejected-pane" role="tabpanel" aria-labelledby="rejected-tab">
        <div class="table-responsive shadow-sm" style="border-radius: 8px;">
            <table class="table table-hover align-middle m-0 bg-white">
                <thead class="table-light text-secondary">
                    <tr>
                        <th style="width: 60px;" class="text-center">STT</th>
                        <th>Lớp học</th>
                        <th style="width: 180px;">Học Viên</th>
                        <th style="width: 140px;" class="text-center">Ngày Xin Nghỉ</th>
                        <th style="text-align: left; padding-left: 15px;">Lý do học viên ghi</th>
                        <th style="width: 150px;" class="text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rejectedRequests as $index => $req)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td><span class="fw-bold text-dark fs-7">{{ $req->lessonSession->courseClass->class_name ?? 'N/A' }}</span></td>
                        <td class="fw-medium">
                            <span class="text-dark d-block">{{ $req->student->name ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center text-secondary fw-medium">{{ \Carbon\Carbon::parse($req->request_date)->format('d/m/Y') }}</td>
                        <td style="text-align: left; padding-left: 15px;" class="text-muted fs-7">{{ $req->reason }}</td>
                        <td class="text-center"><span class="badge bg-danger-subtle text-danger fw-bold border border-danger-subtle px-2 py-1 fs-8" style="border-radius: 4px;"><i class="bi bi-x"></i> Đã từ chối</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center p-4 text-muted">Chưa có đơn nào bị từ chối.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL XÁC NHẬN TỪ CHỐI NHANH (Không nhập lý do) --}}
<div class="modal fade" id="confirmRejectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px;">
            <div class="modal-header bg-danger text-white py-2" style="border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h6 class="modal-title fw-bold" id="confirmRejectModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Xác nhận hủy đơn</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <div class="modal-body p-3 text-center">
                    <p class="mb-0 text-dark fs-7">Bạn có chắc chắn muốn <strong>Từ chối</strong> đơn xin nghỉ của học viên <strong id="studentNameTarget"></strong>?</p>
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-end gap-2 p-2" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                    <button type="button" class="btn btn-xs btn-secondary px-3 py-1 fw-medium" data-bs-dismiss="modal" style="font-size: 13px; border-radius: 4px;">Hủy</button>
                    <button type="submit" class="btn btn-xs btn-danger text-white px-3 py-1 fw-bold" style="font-size: 13px; border-radius: 4px;">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tab Handler Bootstrap
        var triggerTabList = [].slice.call(document.querySelectorAll('#leaveStatusTabs button'))
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                triggerTabList.forEach(btn => {
                    btn.classList.remove('text-dark');
                    btn.classList.add('text-secondary');
                    btn.style.borderBottom = 'none';
                });
                this.classList.remove('text-secondary');
                this.classList.add('text-dark');
                this.style.setProperty('border-bottom', '3px solid #DF8A14', 'important');
                tabTrigger.show()
            })
        });

        // Bắt sự kiện bấm nút Từ chối để điền thông tin lên Modal
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