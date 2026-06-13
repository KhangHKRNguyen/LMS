@extends('layouts.admin')

@section('title', 'Quản lý tài khoản')

@section('admin_content')
<div class="text-center mb-5">
    <h3 class="text-danger fw-bold" style="letter-spacing: 1px;">DANH SÁCH TÀI KHOẢN</h3>
</div>

{{-- Hiển thị thông báo Alert nếu có thành công/thất bại --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    {{-- Form Tìm kiếm thực tế --}}
    <form method="GET" action="{{ route('admin.accounts.index') }}" class="position-relative" style="width: 320px;">
        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
            <i class="bi bi-search"></i>
        </span>
        <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-5 border-secondary-subtle" placeholder="Tìm theo tên đăng nhập, tên hoặc email..." style="border-radius: 6px; height: 42px;" onchange="this.form.submit()">
    </form>
    
    <a href="{{ route('admin.accounts.create') }}" class="btn text-dark fw-semibold" style="background-color: #E2E8F0; border: 1px solid #CBD5E1; padding: 8px 24px; border-radius: 4px;">
        Tạo tài khoản
    </a>
</div>

<div class="table-responsive shadow-sm" style="border-radius: 8px;">
    <table class="table-arena m-0">
        <thead>
            <tr>
                <th style="width: 50px;">STT</th>
                <th>Mã người dùng</th>
                <th>Họ tên</th>
                <th>Vai trò</th>
                <th>Email</th>
                <th>Trạng thái</th>
                <th style="width: 250px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $index => $account)
            <tr>
                <td>{{ ($accounts->currentPage() - 1) * $accounts->perPage() + $loop->iteration }}</td>
                <td><span class="fw-bold text-dark">{{ $account->id }}</span></td>
                <td>{{ $account->name }}</td>
                <td>
                    @if($account->role === 'admin') <span class="badge bg-danger">Admin</span>
                    @elseif($account->role === 'teacher') <span class="badge bg-primary">Giảng viên</span>
                    @elseif($account->role === 'ta') <span class="badge bg-info text-dark">Trợ lý (TA)</span>
                    @else <span class="badge bg-secondary">Học viên</span>
                    @endif
                </td>
                <td>{{ $account->email }}</td>
                <td>
                    @if($account->status === 'active')
                        <span class="badge-status status-active">Đang hoạt động</span>
                    @else
                        <span class="badge-status status-locked">Đã khóa</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex justify-content-center gap-2">
                        
                        @if($account->id !== auth()->id() && $account->role !== 'admin')
                            {{-- Khóa / Mở khóa --}}
                            @if($account->status === 'active')
                                <button onclick="openStatusModal('{{ route('admin.accounts.toggleStatus', $account->id) }}', 'khóa')" class="btn btn-sm btn-warning px-3 fw-medium" style="font-size: 12px; background-color: #FEF08A; border: 1px solid #EAB308; color: #713F12;">Khóa</button>
                            @else
                                <button onclick="openStatusModal('{{ route('admin.accounts.toggleStatus', $account->id) }}', 'mở khóa')" class="btn btn-sm btn-primary px-3 fw-medium" style="font-size: 12px; background-color: #DBEAFE; border: 1px solid #3B82F6; color: #1E40AF;">Mở khóa</button>
                            @endif

                            {{-- Xóa tài khoản --}}
                            <button onclick="openDeleteModal('{{ route('admin.accounts.destroy', $account->id) }}')" class="btn btn-sm btn-danger px-3 fw-medium" style="font-size: 12px; background-color: #FEE2E2; border: 1px solid #EF4444; color: #9B1C1C;">Xóa</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy tài khoản nào dữ liệu phù hợp.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Phân trang chuẩn Laravel --}}
<div class="d-flex justify-content-center mt-4">
    {{ $accounts->links() }}
</div>

{{-- MODAL XÓA CHUNG --}}
<div id="deleteAccountModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #FCA5A5; border: 2px solid #990000; padding: 30px; border-radius: 8px; width: 440px; text-align: center;">
        <h5 class="fw-bold text-dark mb-4">Bạn có chắc muốn xóa tài khoản này không?</h5>
        <div class="d-flex justify-content-center gap-3">
            <form id="deleteAccountForm" method="POST" action="">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-dark text-white px-4" style="background: #990000; border: none; border-radius: 20px;">Đồng ý</button>
            </form>
            <button onclick="closeModal('deleteAccountModal')" class="btn bg-white text-dark px-4" style="border-radius: 20px; border: 1px solid #CBD5E1;">Hủy</button>
        </div>
    </div>
</div>

{{-- MODAL KHÓA / MỞ KHÓA CHUNG --}}
<div id="statusAccountModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #FEF08A; border: 2px solid #EAB308; padding: 30px; border-radius: 8px; width: 440px; text-align: center;">
        <h5 id="statusModalTitle" class="fw-bold text-dark mb-4">Bạn có chắc muốn thay đổi trạng thái không?</h5>
        <div class="d-flex justify-content-center gap-3">
            <form id="statusAccountForm" method="POST" action="">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-warning text-dark fw-bold px-4" style="border-radius: 20px;">Xác nhận</button>
            </form>
            <button onclick="closeModal('statusAccountModal')" class="btn bg-white text-dark px-4" style="border-radius: 20px; border: 1px solid #CBD5E1;">Hủy</button>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(url) {
        document.getElementById('deleteAccountForm').setAttribute('action', url);
        document.getElementById('deleteAccountModal').style.display = 'flex';
    }

    function openStatusModal(url, textAction) {
        document.getElementById('statusAccountForm').setAttribute('action', url);
        document.getElementById('statusModalTitle').innerText = `Bạn có chắc muốn ${textAction} tài khoản này không?`;
        document.getElementById('statusAccountModal').style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
</script>
@endsection